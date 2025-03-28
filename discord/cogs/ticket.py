import discord
from discord.ext import commands
from discord import app_commands, utils
import asyncio
import os
from datetime import datetime

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

            # Obtener rol de moderador
            modrole = interaction.guild.get_role(1333787751031635968)
            if not modrole:
                return await interaction.response.send_message(
                    "❌ Rol de moderador no encontrado",
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
        
        # Verificar si el canal ya está cerrado
        if interaction.channel.name.startswith("🎫┇closed-"):
            return await interaction.channel.send(
                "⚠️ Este ticket ya está cerrado",
                delete_after=5
            )
        
        # Obtener categoría Closed-
        current_category = interaction.channel.category
        closed_category_name = f"Closed-{current_category.name}" if current_category else "Closed-Tickets"
        closed_category = utils.get(interaction.guild.categories, name=closed_category_name)
        
        if not closed_category:
            closed_category = await interaction.guild.create_category(closed_category_name)
        
        # Cambiar nombre y categoría
        new_name = interaction.channel.name.replace("🎫┇", "🎫┇closed-")
        await interaction.channel.edit(
            category=closed_category,
            name=new_name[:100],
            reason=f"Ticket cerrado por {interaction.user}"
        )
        
        # Enviar embed de cierre
        embed = discord.Embed(
            title="🔒 Ticket Cerrado",
            description="Elige una opción:",
            color=discord.Color.red()
        )
        
        view = PostCloseActions(self.bot)
        await interaction.channel.send(embed=embed, view=view)

    @discord.ui.button(
        label="Cancelar",
        style=discord.ButtonStyle.grey,
        custom_id="persistent_view:cancel_close",
        emoji="❌"
    )
    async def cancel_close(self, interaction: discord.Interaction, button: discord.ui.Button):
        await interaction.message.delete()

# =============================================
# ACCIONES POST-CIERRE (VERSIÓN SIMPLIFICADA)
# =============================================
class PostCloseActions(discord.ui.View):
    def __init__(self, bot):
        super().__init__(timeout=None)
        self.bot = bot

    @discord.ui.button(
        label="Eliminar Ticket",
        style=discord.ButtonStyle.red,
        custom_id="persistent_view:delete_ticket",
        emoji="🗑️"
    )
    async def delete_ticket(self, interaction: discord.Interaction, button: discord.ui.Button):
        await interaction.response.defer()
        
        # 1. Primero generar y enviar el transcript
        transcript_success = await self._generate_transcript(interaction)
        
        if not transcript_success:
            # Si falla el transcript, no continuar con la eliminación
            return await interaction.followup.send(
                "❌ Error al generar transcript, no se eliminó el ticket",
                ephemeral=True
            )
        
        # 2. Eliminar el mensaje original de confirmación
        await interaction.delete_original_response()
        
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
        TRANSCRIPT_CHANNEL_ID = 1354891719400886472  # REEMPLAZA CON TU ID
        SAVE_FOLDER = "transcripts"
        filename = f"{interaction.channel.id}.txt"
        filepath = os.path.join(SAVE_FOLDER, filename)
        
        try:
            # 1. Preparar sistema de archivos
            os.makedirs(SAVE_FOLDER, exist_ok=True)
            
            # 2. Verificar si ya existe
            transcript_channel = self.bot.get_channel(TRANSCRIPT_CHANNEL_ID)
            if not transcript_channel:
                print(f"[ERROR] Canal de transcripts no encontrado: {TRANSCRIPT_CHANNEL_ID}")
                return False
                
            # 3. Generar contenido
            transcript_content = [
                f"=== TRANSCRIPT DEL TICKET ===\n",
                f"Canal: {interaction.channel.name} (ID: {interaction.channel.id})\n",
                f"Creado: {interaction.channel.created_at.strftime('%Y-%m-%d %H:%M:%S')}\n",
                f"Generado: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}\n",
                f"Por: {interaction.user.display_name} (ID: {interaction.user.id})\n",
                "="*50 + "\n\n"
            ]
            
            # Procesar mensajes
            async for message in interaction.channel.history(limit=None, oldest_first=True):
                entry = [
                    f"[{message.created_at.strftime('%Y-%m-%d %H:%M:%S')}] ",
                    f"{message.author.display_name} ({message.author.id}):\n"
                ]
                
                if message.content:
                    entry.append(f"{message.content}\n")
                if message.edited_at:
                    entry.append(f"(Editado: {message.edited_at.strftime('%Y-%m-%d %H:%M:%S')}\n")
                if message.attachments:
                    entry.append("Archivos adjuntos:\n")
                    entry.extend([f"- {a.filename}: {a.url}\n" for a in message.attachments])
                if message.embeds:
                    entry.append("Contenido embebido:\n")
                    for embed in message.embeds:
                        if embed.title:
                            entry.append(f"Título: {embed.title}\n")
                        if embed.description:
                            entry.append(f"Descripción: {embed.description}\n")
                        for field in embed.fields:
                            entry.append(f"{field.name}: {field.value}\n")
                
                transcript_content.extend(entry)
                transcript_content.append("-"*50 + "\n")

            # 4. Guardar archivo localmente
            with open(filepath, 'w', encoding='utf-8') as f:
                f.writelines(transcript_content)

            # 5. Enviar al canal de transcripts
            with open(filepath, 'rb') as f:
                await transcript_channel.send(
                    content=f"📄 Transcript de {interaction.channel.mention}",
                    file=discord.File(f, filename=f"transcript_{interaction.channel.name}.txt")
                )
            
            return True
            
        except Exception as e:
            print(f"[ERROR-TRANSCRIPT] {type(e).__name__}: {e}")
            await interaction.followup.send(
                f"❌ Error al generar transcript: {str(e)}",
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
        
        # Verificar si ya está abierto
        if not interaction.channel.name.startswith("🎫┇closed-"):
            return await interaction.followup.send(
                "⚠️ Este ticket ya está abierto",
                ephemeral=True,
                delete_after=5
            )
        
        # Obtener categoría original
        current_category = interaction.channel.category
        if not current_category or not current_category.name.startswith("Closed-"):
            return await interaction.followup.send(
                "❌ No se puede determinar la categoría original",
                ephemeral=True,
                delete_after=5
            )
        
        original_category_name = current_category.name.replace("Closed-", "")
        original_category = utils.get(interaction.guild.categories, name=original_category_name)
        
        if not original_category:
            return await interaction.followup.send(
                "❌ No se encontró la categoría original",
                ephemeral=True,
                delete_after=5
            )
        
        # Restaurar nombre y categoría
        new_name = interaction.channel.name.replace("🎫┇closed-", "🎫┇")
        await interaction.channel.edit(
            category=original_category,
            name=new_name[:100],
            reason=f"Ticket reabierto por {interaction.user}"
        )
        
        # Enviar confirmación temporal
        embed = discord.Embed(
            title="🔓 Ticket Reabierto",
            description="Ahora puedes continuar con tu consulta.",
            color=discord.Color.green()
        )
        
        msg = await interaction.channel.send(embed=embed)
        await interaction.delete_original_response()
        await asyncio.sleep(3)
        await msg.delete()
        
# =============================================
# COG PRINCIPAL
# =============================================
class TicketSystem(commands.Cog):
    def __init__(self, bot):
        self.bot = bot
        self.persistent_views_added = False

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

    @app_commands.command(name="panel", description="Crea el panel de tickets")
    @app_commands.default_permissions(manage_guild=True)
    @app_commands.checks.cooldown(1, 60, key=lambda i: (i.guild_id))
    @app_commands.checks.bot_has_permissions(manage_channels=True)
    async def ticket_panel(self, interaction: discord.Interaction):
        try:
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