import discord
from discord.ext import commands
from discord import app_commands, utils
import asyncio
import os
import json
from datetime import datetime
from pathlib import Path

# =============================================
# CLASE PARA CONFIGURACIÓN
# =============================================
class TicketConfig:
    def __init__(self):
        self.config_path = Path('./cogs/json/ticket.json')
        self.config_path.parent.mkdir(parents=True, exist_ok=True)
        
        if not self.config_path.exists():
            with open(self.config_path, 'w') as f:
                json.dump({}, f)
    
    def get_guild_config(self, guild_id):
        with open(self.config_path, 'r') as f:
            config = json.load(f)
            return config.get(str(guild_id), {})
    
    def update_guild_config(self, guild_id, **kwargs):
        with open(self.config_path, 'r+') as f:
            config = json.load(f)
            guild_id = str(guild_id)
            
            if guild_id not in config:
                config[guild_id] = {"modrol": None, "transcript_id": None}
            
            for key, value in kwargs.items():
                config[guild_id][key] = value
            
            f.seek(0)
            json.dump(config, f, indent=4)
            f.truncate()

# =============================================
# CLASE PARA EL SELECT DE AYUDA
# =============================================
class HelpSelect(discord.ui.Select):
    def __init__(self):
        options = [
            discord.SelectOption(
                label="Soporte", 
                value="sop", 
                emoji="🔨", 
                description="Problemas técnicos"
            ),
            discord.SelectOption(
                label="Dudas", 
                value="dud", 
                emoji="❓", 
                description="Preguntas generales"
            ),
            discord.SelectOption(
                label="Aportaciones", 
                value="aport", 
                emoji="💵", 
                description="Donaciones"
            )
        ]
        super().__init__(
            placeholder="Selecciona una categoría...",
            min_values=1,
            max_values=1,
            options=options,
            custom_id="persistent_view:help_select"
        )

    async def callback(self, interaction: discord.Interaction):
        try:
            # Verificar tickets existentes
            ticket_type = self.values[0]
            user_tickets = [
                channel for channel in interaction.guild.text_channels 
                if channel.name.startswith(f"🎫┇{ticket_type}-{interaction.user.name}")
            ]
            
            if len(user_tickets) >= 2:
                await interaction.response.send_message(
                    f"❌ Ya tienes 2 tickets abiertos en {ticket_type.upper()}",
                    ephemeral=True
                )
                return

            # Obtener configuración del servidor
            config = TicketConfig().get_guild_config(interaction.guild.id)
            modrole = interaction.guild.get_role(config.get("modrol"))
            
            if not modrole:
                return await interaction.response.send_message(
                    "❌ Rol de moderador no configurado. Usa /setup para configurarlo.",
                    ephemeral=True
                )

            # Mapeo de categorías
            category_map = {
                "sop": "Soporte",
                "dud": "Dudas",
                "aport": "Aportaciones"
            }
            
            # Crear categoría si no existe
            category_name = category_map[ticket_type]
            category = utils.get(interaction.guild.categories, name=category_name)
            
            if not category:
                category = await interaction.guild.create_category(category_name)
            
            closed_category_name = f"Closed-{category_name}"
            closed_category = utils.get(interaction.guild.categories, name=closed_category_name)

            if not closed_category:
                closed_category = await interaction.guild.create_category(closed_category_name)

            # Configurar permisos del canal
            overwrites = {
                interaction.guild.default_role: discord.PermissionOverwrite(view_channel=False),
                interaction.user: discord.PermissionOverwrite(
                    view_channel=True,
                    send_messages=True,
                    read_message_history=True
                ),
                modrole: discord.PermissionOverwrite(
                    view_channel=True,
                    manage_messages=True,
                    manage_channels=True
                ),
                interaction.guild.me: discord.PermissionOverwrite(
                    view_channel=True,
                    manage_channels=True
                )
            }

            # Crear canal del ticket
            channel = await interaction.guild.create_text_channel(
                name=f"🎫┇{ticket_type}-{interaction.user.name}",
                category=category,
                overwrites=overwrites,
                reason=f"Ticket creado por {interaction.user}"
            )

            # Mensaje de confirmación
            await interaction.response.send_message(
                f"✅ Ticket creado: {channel.mention}",
                ephemeral=True
            )

            # Mensaje inicial en el ticket
            embed = discord.Embed(
                title=f"Ticket de {category_name}",
                description="Por favor describe tu consulta en detalle.\nEl equipo te responderá pronto.",
                color=discord.Color.blue()
            )
            embed.set_footer(text=f"Usuario: {interaction.user.display_name}")
            
            # Obtener el bot desde interaction.client
            bot = interaction.client
            await channel.send(
                content=f"👋 {interaction.user.mention}, bienvenid@ a tu ticket.",
                embed=embed,
                view=TicketView(bot)
            )
            
        except Exception as e:
            print(f"[ERROR] Al crear ticket: {str(e)}")
            await interaction.response.send_message(
                "❌ Error crítico al crear el ticket. Contacta con un administrador.",
                ephemeral=True
            )

# =============================================
# VISTA PRINCIPAL DE AYUDA
# =============================================
class HelpView(discord.ui.View):
    def __init__(self):
        super().__init__(timeout=None)
        self.add_item(HelpSelect())

# =============================================
# VISTA DEL TICKET (CONTROLES)
# =============================================
class TicketView(discord.ui.View):
    def __init__(self, bot):
        super().__init__(timeout=None)
        self.bot = bot

    @discord.ui.button(
        label="Cerrar Ticket",
        style=discord.ButtonStyle.red,
        custom_id="persistent_view:close_ticket",
        emoji="🔒"
    )
    async def close_ticket(self, interaction: discord.Interaction, button: discord.ui.Button):
        await interaction.response.defer()
        
        embed = discord.Embed(
            title="⚠️ Confirmar cierre",
            description="¿Estás seguro de que quieres cerrar este ticket?",
            color=discord.Color.orange()
        )
        
        await interaction.channel.send(
            embed=embed,
            view=ConfirmClose(self.bot)
        )

# =============================================
# VISTA DE CONFIRMACIÓN DE CIERRE
# =============================================

class ConfirmClose(discord.ui.View):
    def __init__(self, bot):
        super().__init__(timeout=None)
        self.bot = bot

    @discord.ui.button(
        label="Confirmar",
        style=discord.ButtonStyle.green,
        custom_id="persistent_view:confirm_close",
        emoji="✅"
    )
    async def confirm_close(self, interaction: discord.Interaction, button: discord.ui.Button):
        await interaction.message.delete()
        
        # 1. Verificar si ya está cerrado
        if interaction.channel.name.startswith("🎫┇closed-"):
            return await interaction.channel.send("⚠️ Este ticket ya está cerrado", delete_after=5)
        
        # 2. Obtener rol de moderador
        config = TicketConfig().get_guild_config(interaction.guild.id)
        modrole = interaction.guild.get_role(config.get("modrol"))
        if not modrole:
            return await interaction.channel.send("❌ Rol de moderador no configurado", delete_after=10)
        
        # 3. Encontrar la categoría "Closed" correspondiente
        original_category = interaction.channel.category
        closed_category_name = f"Closed-{original_category.name}"
        closed_category = utils.get(interaction.guild.categories, name=closed_category_name)
        
        # Si no existe la categoría closed, la creamos
        if not closed_category:
            closed_category = await interaction.guild.create_category(closed_category_name)
            # Configurar permisos para la nueva categoría
            await closed_category.set_permissions(
                interaction.guild.default_role,
                view_channel=False,
                send_messages=False
            )
            await closed_category.set_permissions(
                modrole,
                view_channel=True,
                manage_channels=True,
                send_messages=True
            )
        
        # 4. Configurar permisos del canal
        overwrites = {
            interaction.guild.default_role: discord.PermissionOverwrite(
                view_channel=False  # Los nuevos miembros no ven el ticket
            ),
            modrole: discord.PermissionOverwrite(
                view_channel=True,
                send_messages=True,  # Mods pueden escribir
                manage_messages=True
            )
        }
        
        # 5. Mantener acceso a TODOS los participantes actuales
        async for message in interaction.channel.history(limit=200):
            if not message.author.bot and message.author not in overwrites:
                overwrites[message.author] = discord.PermissionOverwrite(
                    view_channel=True,    # Pueden VER
                    send_messages=False,  # NO pueden ESCRIBIR
                    read_message_history=True
                )
        
        # 6. Aplicar cambios (incluyendo mover a la categoría closed)
        await interaction.channel.edit(
            name=f"🎫┇closed-{interaction.channel.name.split('┇')[-1]}",
            category=closed_category,  # Esta línea mueve el canal a la categoría closed
            overwrites=overwrites,
            reason=f"Cierre de ticket por {interaction.user}"
        )
        
        # 7. Mensaje de confirmación
        embed = discord.Embed(
            title="🔒 Ticket Cerrado",
            description="Este ticket ha sido cerrado.\nSi se desea **reabrir** este ticket, haga click en el botón de *reabir*\nEn caso negativo, porfavor, elimine el ticket.",
            color=discord.Color.red()
        )
        await interaction.channel.send(embed=embed, view=PostCloseActions(self.bot))

    @discord.ui.button(
        label="Cancelar",
        style=discord.ButtonStyle.grey,
        custom_id="persistent_view:cancel_close",
        emoji="❌"
    )
    async def cancel_close(self, interaction: discord.Interaction, button: discord.ui.Button):
        await interaction.message.delete()

# =============================================
# ACCIONES POST-CIERRE
# =============================================
class PostCloseActions(discord.ui.View):
    def __init__(self, bot):
        super().__init__(timeout=None)
        self.bot = bot

    async def interaction_check(self, interaction: discord.Interaction) -> bool:
        """Verifica que el usuario tenga el rol de moderador."""
        config = TicketConfig().get_guild_config(interaction.guild.id)
        modrole = interaction.guild.get_role(config.get("modrol"))
        
        if not modrole or modrole not in interaction.user.roles:
            await interaction.response.send_message(
                "❌ No tienes permisos para realizar esta acción.",
                ephemeral=True
            )
            return False
        return True

    @discord.ui.button(
        label="Eliminar Ticket",
        style=discord.ButtonStyle.red,
        custom_id="persistent_view:delete_ticket",
        emoji="🗑️"
    )
    async def delete_ticket(self, interaction: discord.Interaction, button: discord.ui.Button):
        await interaction.response.defer()  # Diferir la respuesta inicial

        config = TicketConfig().get_guild_config(interaction.guild.id)
        TRANSCRIPT_CHA = config.get("transcript_id")

        if not TRANSCRIPT_CHA:
            await interaction.followup.send(
                "❌ Canal de transcripts no configurado. Usa el panel de configuración para configurarlo.",
                ephemeral=True
            )
            return False

        # Crear y enviar embed de espera
        waiting = discord.Embed(
            title='Generando transcript...',
            description=f'El transcript está siendo generado para\nel canal <#{TRANSCRIPT_CHA}>, por favor espere',
            color=discord.Color.from_rgb(39, 118, 223),
        )
        file = discord.File("./cogs/banner/standard.gif", filename="standard.gif")
        waiting.set_image(url="attachment://standard.gif")
        await interaction.delete_original_response()
        # Enviar el embed y guardar el mensaje para poder borrarlo después
        waiting_message = await interaction.channel.send(embed=waiting, file=file)

        try:
            # Generar el transcript
            transcript_success = await self._generate_transcript(interaction)
            
            # Borrar el mensaje de espera
            await waiting_message.delete()
            
            # Aquí podrías añadir más lógica según el resultado de transcript_success
            if not transcript_success:
                await interaction.followup.send(
                    "❌ Error al generar el transcript",
                    ephemeral=True
                )
                return False
                
        except Exception as e:
            await interaction.followup.send(
                f"❌ Ocurrió un error: {str(e)}",
                ephemeral=True
            )
            return False
        
        # 3. Enviar nuevo embed de cuenta regresiva
        countdown = discord.Embed(
            title="⏳ Eliminando ticket...",
            description="El ticket se eliminará en 5 segundos",
            color=discord.Color.red()
        )
        countdown_msg = await interaction.channel.send(embed=countdown)
        
        # 4. Espera dramática
        for i in range(5, 0, -1):
            countdown.description = f"El ticket se eliminará en {i} segundos..."
            await countdown_msg.edit(embed=countdown)
            await asyncio.sleep(1)
        
        # 5. Eliminar el canal
        await interaction.channel.delete(
            reason=f"Ticket eliminado por {interaction.user}"
        )

    async def _generate_transcript(self, interaction: discord.Interaction) -> bool:
        """Función interna para generar el transcript. Devuelve True si fue exitoso."""
        config = TicketConfig().get_guild_config(interaction.guild.id)
        TRANSCRIPT_CHANNEL_ID = config.get("transcript_id")
        
        if not TRANSCRIPT_CHANNEL_ID:
            await interaction.followup.send(
                "❌ Canal de transcripts no configurado. Usa el panel de configuración para configurarlo.",
                ephemeral=True
            )
            return False
            
        SAVE_FOLDER = "transcripts"
        os.makedirs(SAVE_FOLDER, exist_ok=True)
        filename = f"transcript_{interaction.channel.id}.txt"
        filepath = os.path.join(SAVE_FOLDER, filename)
        
        try:
            # Obtener canal de transcripts
            transcript_channel = self.bot.get_channel(int(TRANSCRIPT_CHANNEL_ID))
            if not transcript_channel:
                print(f"[ERROR] Canal de transcripts no encontrado: {TRANSCRIPT_CHANNEL_ID}")
                return False

            # Obtener el creador REAL del ticket (usuario mencionado en el primer mensaje del bot)
            creator = None
            async for message in interaction.channel.history(limit=10, oldest_first=True):
                if message.author == self.bot.user and message.mentions:
                    creator = message.mentions[0]  # El usuario mencionado en el primer mensaje del bot
                    break
            
            # Si no encontramos al creador, usamos el usuario que está cerrando el ticket como fallback
            if not creator:
                creator = interaction.user

            # Obtener tiempos exactos
            creation_time = interaction.channel.created_at
            current_time = datetime.now()
            time_format = '%Y-%m-%d %H:%M:%S'
            
            # Procesar mensajes y participantes
            participants = set()
            category_name = interaction.channel.category.name.replace("Closed-", "")
            channel_name = interaction.channel.name.replace("🎫┇", "")
            transcript_content = [
                f"=== TRANSCRIPT DEL TICKET ===\n",
                f"• Canal: {channel_name}\n",
                f"• ID del Canal: {interaction.channel.id}\n",
                f"• Categoría: {category_name}\n"  # <-- Nombre limpio
                f"• Creado por: {creator.display_name} (ID: {creator.id})\n",
                "="*50 + "\n\n"
            ]

            # Procesar historial de mensajes
            async for message in interaction.channel.history(limit=None, oldest_first=True):
                participants.add(message.author)
                
                entry = [
                    f"[{message.created_at.strftime(time_format)}] ",
                    f"{message.author.display_name} ({message.author.id}):\n",
                    f"{message.content}\n" if message.content else ""
                ]
                
                if message.edited_at:
                    entry.append(f"(Editado: {message.edited_at.strftime(time_format)})\n")
                
                if message.attachments:
                    entry.append("Archivos adjuntos:\n")
                    entry.extend(f"- {a.filename}: {a.url}\n" for a in message.attachments)
                
                if message.embeds:
                    entry.append("Contenido embebido:\n")
                    for embed in message.embeds:
                        if embed.title: entry.append(f"Título: {embed.title}\n")
                        if embed.description: entry.append(f"Descripción: {embed.description}\n")
                        for field in embed.fields:
                            entry.append(f"{field.name}: {field.value}\n")
                
                transcript_content.extend(entry)
                transcript_content.append("-"*50 + "\n")

            # Guardar archivo local
            with open(filepath, 'w', encoding='utf-8') as f:
                f.writelines(transcript_content)

            # Crear embed visual
            category_name = interaction.channel.category.name.replace("Closed-", "")
            channel_name = interaction.channel.name.replace("🎫┇", "")
            transcript_embed = discord.Embed(
                title=f"📝 Transcript de {category_name}",  # Usamos el nombre limpio
                description=f'{channel_name}',
                color=discord.Color.from_rgb(39, 118, 223),
                timestamp=current_time
            )
            
            # Configurar ambas imágenes (thumbnail y banner)
            files = []
            try:
                # Thumbnail (logo pequeño)
                logo_file = discord.File("./cogs/banner/logo.png", filename="logo.png")
                transcript_embed.set_thumbnail(url="attachment://logo.png")
                transcript_embed.set_footer(
                    text=f"Ticket ID: {interaction.channel.id}",
                    icon_url="attachment://logo.png"
                )
                files.append(logo_file)
                
                # Banner grande
                banner_file = discord.File("./cogs/banner/standard.gif", filename="standard.gif")
                transcript_embed.set_image(url="attachment://standard.gif")
                files.append(banner_file)
            except Exception as e:
                print(f"[WARNING] No se pudieron cargar imágenes: {e}")

            # Añadir campos al embed
            transcript_embed.add_field(
                name="👤 Creador del Ticket",
                value=f"{creator.mention}\nID: {creator.id}",
                inline=True
            )
            
            participants_text = "\n".join(f"• {p.display_name} (ID: {p.id})" for p in participants if p != creator)
            transcript_embed.add_field(
                name=f"👥 Participantes ({len(participants)-1})",
                value=participants_text or "No hay otros participantes",
                inline=False
            )

            # Enviar primero el embed con las imágenes
            await transcript_channel.send(
                embed=transcript_embed,
                files=files
            )
            
            # Luego enviar el archivo TXT por separado
            await transcript_channel.send(
                file=discord.File(filepath)
            )
            
            return True
            
        except Exception as e:
            print(f"[ERROR-TRANSCRIPT] {datetime.now().strftime(time_format)} - Error:", e)
            await interaction.followup.send(
                "❌ Error al generar el transcript. Contacta con un administrador.",
                ephemeral=True
            )
            return False

    @discord.ui.button(
        label="Re-Abrir",
        style=discord.ButtonStyle.green,
        custom_id="persistent_view:reopen_ticket",
        emoji="🔓"
    )
    async def reopen_ticket(self, interaction: discord.Interaction, button: discord.ui.Button):
        await interaction.response.defer()
        
        # 1. Verificar estado
        if not interaction.channel.name.startswith("🎫┇closed-"):
            return await interaction.followup.send("⚠️ Ya está abierto", ephemeral=True, delete_after=5)
        
        # 2. Obtener configuración
        config = TicketConfig().get_guild_config(interaction.guild.id)
        modrole = interaction.guild.get_role(config.get("modrol"))
        
        # 3. Configurar permisos de reapertura
        overwrites = {
            interaction.guild.default_role: discord.PermissionOverwrite(view_channel=False),
            modrole: discord.PermissionOverwrite(
                view_channel=True,
                send_messages=True,
                manage_messages=True
            )
        }
        
        # 4. Restaurar escritura a participantes
        async for message in interaction.channel.history(limit=200):
            if not message.author.bot and message.author not in overwrites:
                overwrites[message.author] = discord.PermissionOverwrite(
                    view_channel=True,
                    send_messages=True,  # Ahora SÍ pueden escribir
                    read_message_history=True
                )
        
        # 5. Aplicar cambios
        await interaction.channel.edit(
            name=f"🎫┇{interaction.channel.name.split('closed-')[-1]}",
            overwrites=overwrites,
            reason=f"Reapertura por {interaction.user}"
        )
        
        # 6. Confirmación efímera
        await interaction.followup.send("✅ Ticket reabierto correctamente", ephemeral=True)
        await interaction.channel.send(f"🔓 {interaction.user.mention} ha reabierto este ticket")

# =============================================
# COG PRINCIPAL
# =============================================
class TicketSystem(commands.Cog):
    def __init__(self, bot):
        self.bot = bot
        self.persistent_views_added = False
        self.config = TicketConfig()

    async def setup_hook(self):
        if not self.persistent_views_added:
            # Registrar todas las vistas persistentes
            self.bot.add_view(HelpView())
            self.bot.add_view(TicketView(self.bot))
            self.bot.add_view(ConfirmClose(self.bot))
            self.bot.add_view(PostCloseActions(self.bot))
            self.persistent_views_added = True
            print("[Tickets] Vistas persistentes registradas")

    @commands.Cog.listener()
    async def on_ready(self):
        # Volver a registrar vistas por si acaso
        self.bot.add_view(HelpView())
        self.bot.add_view(TicketView(self.bot))
        self.bot.add_view(ConfirmClose(self.bot))
        self.bot.add_view(PostCloseActions(self.bot))
        print("[Tickets] Vistas persistentes re-registradas en on_ready")

    @commands.Cog.listener()
    async def on_guild_join(self, guild):
        self.config.update_guild_config(guild.id)
        print(f"[Tickets] Configuración creada para el servidor: {guild.name} ({guild.id})")

    @commands.Cog.listener()
    async def on_guild_remove(self, guild):
        with open(self.config.config_path, 'r+') as f:
            config = json.load(f)
            config.pop(str(guild.id), None)
            f.seek(0)
            json.dump(config, f, indent=4)
            f.truncate()
        print(f"[Tickets] Configuración eliminada para el servidor: {guild.name} ({guild.id})")

    @app_commands.command(name="panel", description="Crea el panel de tickets")
    @app_commands.default_permissions(manage_guild=True)
    @app_commands.checks.cooldown(1, 60, key=lambda i: (i.guild_id))
    @app_commands.checks.bot_has_permissions(manage_channels=True)
    async def ticket_panel(self, interaction: discord.Interaction, 
                          modrol: discord.Role, 
                          transcript_channel: discord.TextChannel):
        self.config.update_guild_config(
            interaction.guild.id,
            modrol=modrol.id,
            transcript_id=transcript_channel.id
        )
        try:
            # Verificar configuración
            config = self.config.get_guild_config(interaction.guild.id)
            if not config.get("modrol") or not config.get("transcript_id"):
                return await interaction.response.send_message(
                    "❌ Primero debes configurar el sistema con /setup",
                    ephemeral=True
                )

            embed = discord.Embed(
                title="Creando panel....",
                description="El panel de tickets se está creando......",
                color=discord.Color.from_rgb(39, 118, 223)
            )
            
            # Attach the local image file
            file = discord.File("./cogs/banner/standard.gif", filename="standard.gif")
            embed.set_image(url="attachment://standard.gif")
            
            await interaction.response.send_message(embed=embed, file=file, ephemeral=True)

            # Wait for 1 second, then delete the original response
            await asyncio.sleep(1)
            await interaction.delete_original_response()
            await asyncio.sleep(0.5)

            # Send the final ticket panel
            embed2 = discord.Embed(
                title="Apertura de Ticket",
                description="Para abrir un ticket, selecciona una de las opciones:\n🔨Soporte: Explica cual es tu problema/bugg\n❓Dudas: Explica cuales son tus dudas para poder resolverlas\n💵Aportaciones: Puedes realizar una donación para\nmejorar y mantener activo el servicio",
                color=discord.Color.from_rgb(39, 118, 223)
            )
            
            await interaction.channel.send(
                embed=embed2,
                view=HelpView()
            )
            
        except Exception as e:
            await interaction.response.send_message(
                f"Error: {str(e)}", 
                ephemeral=True
            )

async def setup(bot):
    cog = TicketSystem(bot)
    await bot.add_cog(cog)
    await cog.setup_hook()
    