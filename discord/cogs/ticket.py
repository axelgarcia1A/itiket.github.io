import discord, pytz
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

            config = TicketConfig().get_guild_config(interaction.guild.id)
            modrole = interaction.guild.get_role(config.get("modrol"))
            
            if not modrole:
                return await interaction.response.send_message(
                    "❌ Rol de moderador no configurado. Usa /setup para configurarlo.",
                    ephemeral=True
                )

            category_map = {
                "sop": "Soporte",
                "dud": "Dudas",
                "aport": "Aportaciones"
            }
            
            category_name = category_map[ticket_type]
            category = utils.get(interaction.guild.categories, name=category_name)
            
            if not category:
                category = await interaction.guild.create_category(category_name)
            
            closed_category_name = f"Closed-{category_name}"
            closed_category = utils.get(interaction.guild.categories, name=closed_category_name)

            if not closed_category:
                closed_category = await interaction.guild.create_category(closed_category_name)

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

            channel = await interaction.guild.create_text_channel(
                name=f"🎫┇{ticket_type}-{interaction.user.name}",
                category=category,
                overwrites=overwrites,
                reason=f"Ticket creado por {interaction.user}"
            )

            await interaction.response.send_message(
                f"✅ Ticket creado: {channel.mention}",
                ephemeral=True
            )

            embed = discord.Embed(
                title=f'Sistema de ticketing de {interaction.guild.name}',
                description=f"Por favor describe tu consulta en detalle.\nEl equipo te responderá pronto",
                color=discord.Color.from_rgb(39, 118, 223)
            )
            embed.set_footer(text=f"Usuario: {interaction.user.display_name}")
            
            bot = interaction.client
            await channel.send(
                content=f"Bienvenid@ {interaction.user.mention}, a tu ticket de {category}.",
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

        if interaction.channel.name.startswith("🎫┇closed-"):
            return await interaction.channel.send("⚠️ Este ticket ya está cerrado", delete_after=5)

        config = TicketConfig().get_guild_config(interaction.guild.id)
        modrole = interaction.guild.get_role(config.get("modrol"))
        if not modrole:
            return await interaction.channel.send("❌ Rol de moderador no configurado", delete_after=10)

        original_category = interaction.channel.category
        closed_category_name = f"Closed-{original_category.name}"
        closed_category = utils.get(interaction.guild.categories, name=closed_category_name)

        if not closed_category:
            closed_category = await interaction.guild.create_category(closed_category_name)
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
        
        overwrites = {
            interaction.guild.default_role: discord.PermissionOverwrite(
                view_channel=False  
            ),
            modrole: discord.PermissionOverwrite(
                view_channel=True,
                send_messages=True,  
                manage_messages=True
            )
        }

        async for message in interaction.channel.history(limit=200):
            if not message.author.bot and message.author not in overwrites:
                overwrites[message.author] = discord.PermissionOverwrite(
                    view_channel=True,    
                    send_messages=False,  
                    read_message_history=True
                )

        await interaction.channel.edit(
            name=f"🎫┇closed-{interaction.channel.name.split('┇')[-1]}",
            category=closed_category,  
            overwrites=overwrites,
            reason=f"Cierre de ticket por {interaction.user}"
        )

        embed = discord.Embed(
            title="🔒 Ticket Cerrado",
            description="Este ticket ha sido cerrado.\nSi se desea *Re-Abrir* este ticket, haga click en el botón pertinente de **Re-Abrir**\nEn caso negativo, porfavor, elimine el ticket.",
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
        await interaction.response.defer()  

        config = TicketConfig().get_guild_config(interaction.guild.id)
        TRANSCRIPT_CHA = config.get("transcript_id")

        if not TRANSCRIPT_CHA:
            await interaction.followup.send(
                "❌ Canal de transcripts no configurado. Usa el panel de configuración para configurarlo.",
                ephemeral=True
            )
            return False


        waiting = discord.Embed(
            title='Generando transcript...',
            description=f'El transcript está siendo generado para\nel canal <#{TRANSCRIPT_CHA}>, por favor espere',
            color=discord.Color.from_rgb(39, 118, 223),
        )
        file = discord.File("./cogs/banner/standard.gif", filename="standard.gif")
        waiting.set_image(url="attachment://standard.gif")
        await interaction.delete_original_response()
        waiting_message = await interaction.channel.send(embed=waiting, file=file)

        try:
            transcript_success = await self._generate_transcript(interaction)

            await waiting_message.delete()

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

        countdown = discord.Embed(
            title="⏳ Eliminando ticket...",
            description="El ticket se eliminará en 5 segundos",
            color=discord.Color.red()
        )
        countdown_msg = await interaction.channel.send(embed=countdown)

        for i in range(5, 0, -1):
            countdown.description = f"El ticket se eliminará en {i} segundos..."
            await countdown_msg.edit(embed=countdown)
            await asyncio.sleep(1)

        await interaction.channel.delete(
            reason=f"Ticket eliminado por {interaction.user}"
        )

    async def _generate_transcript(self, interaction: discord.Interaction) -> bool:
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
        filename = f"transcript_{interaction.channel.id}.html"
        filepath = os.path.join(SAVE_FOLDER, filename)
        
        try:
            spain_tz = pytz.timezone('Europe/Madrid')
            time_format = '%d/%m/%Y %H:%M:%S'

            transcript_channel = self.bot.get_channel(int(TRANSCRIPT_CHANNEL_ID))
            if not transcript_channel:
                print(f"[ERROR] Canal de transcripts no encontrado: {TRANSCRIPT_CHANNEL_ID}")
                return False

            creator = None
            async for message in interaction.channel.history(limit=10, oldest_first=True):
                if message.author == self.bot.user and message.mentions:
                    creator = message.mentions[0]  
                    break

            if not creator:
                creator = interaction.user

            current_time = datetime.now().astimezone(spain_tz)
            participants = set()
            channel_name = interaction.channel.name.replace("🎫┇closed-", "")
            
            html_content = f"""<!DOCTYPE html>
            <html lang="es">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Transcript de {channel_name}</title>
                <link rel="icon" href="https://i.ibb.co/Xxy6kXfw/itiket-transparente.png"/>
                <style>
                    :root {{
                        --background-primary: #36393f;
                        --background-secondary: #2f3136;
                        --background-tertiary: #202225;
                        --channel-text-area: #40444b;
                        --text-normal: #dcddde;
                        --text-muted: #72767d;
                        --brand-color: #5865f2;
                        --mention-background: rgba(250, 166, 26, 0.1);
                    }}
                    
                    body {{
                        font-family: 'Whitney', 'Helvetica Neue', Helvetica, Arial, sans-serif;
                        background-color: var(--background-primary);
                        color: var(--text-normal);
                        margin: 0;
                        padding: 0;
                        line-height: 1.5;
                    }}
                    
                    .discord-container {{
                        max-width: 90%;
                        margin: 0 auto;
                        background-color: var(--background-secondary);
                        min-height: 100vh;
                        padding: 20px;
                    }}
                    
                    .header {{
                        background-color: var(--background-tertiary);
                        color: white;
                        padding: 20px;
                        border-radius: 8px;
                        margin-bottom: 20px;
                        text-align: left;
                    }}
                    
                    .message {{
                        display: flex;
                        padding: 8px 16px;
                        position: relative;
                    }}
                    
                    .message:hover {{
                        background-color: rgba(79, 84, 92, 0.16);
                    }}
                    
                    .avatar {{
                        width: 40px;
                        height: 40px;
                        border-radius: 50%;
                        margin-right: 16px;
                        flex-shrink: 0;
                    }}
                    
                    .message-content {{
                        flex-grow: 1;
                        min-width: 0;
                    }}
                    
                    .message-header {{
                        display: flex;
                        align-items: baseline;
                        margin-bottom: 4px;
                    }}
                    
                    .author {{
                        font-weight: 500;
                        color: white;
                        margin-right: 8px;
                    }}
                    
                    .timestamp {{
                        color: var(--text-muted);
                        font-size: 0.75rem;
                        font-weight: 400;
                    }}
                    
                    .message-body {{
                        word-wrap: break-word;
                    }}
                    
                    .edited {{
                        color: var(--text-muted);
                        font-size: 0.75rem;
                        margin-left: 4px;
                    }}
                    
                    .attachments {{
                        margin-top: 8px;
                    }}
                    
                    .attachment-image {{
                        max-width: 400px;
                        max-height: 300px;
                        border-radius: 4px;
                        cursor: pointer;
                    }}
                    
                    .attachment-file {{
                        display: inline-block;
                        background-color: var(--background-tertiary);
                        padding: 10px;
                        border-radius: 4px;
                        color: #00aff4;
                        text-decoration: none;
                    }}
                    
                    .embed {{
                        margin-top: 8px;
                        max-width: 520px;
                        border-left: 4px solid #2776df; 
                        border-radius: 4px;
                        display: flex;
                        background-color: var(--background-tertiary);
                        padding: 8px 16px 8px 12px;
                    }}
                    
                    .embed-content {{
                        flex-grow: 1;
                        min-width: 0;
                    }}
                    
                    .embed-title {{
                        font-weight: 600;
                        margin-bottom: 8px;
                    }}
                    
                    .embed-description {{
                        margin-bottom: 8px;
                    }}
                    
                    .embed-fields {{
                        display: flex;
                        flex-wrap: wrap;
                        gap: 8px;
                        margin-bottom: 8px;
                    }}
                    
                    .embed-field {{
                        flex: 1;
                        min-width: 150px;
                    }}
                    
                    .embed-field-name {{
                        font-weight: 600;
                        margin-bottom: 4px;
                    }}
                    
                    .embed-image-container {{
                        margin-top: 8px;
                    }}
                    
                    .embed-image-main {{
                        max-width: 100%;
                        border-radius: 4px;
                        cursor: pointer;
                    }}
                    
                    .embed-thumbnail {{
                        margin-left: 16px;
                        width: 80px;
                        height: 80px;
                        border-radius: 4px;
                        flex-shrink: 0;
                    }}
                    
                    .embed-footer {{
                        margin-top: 8px;
                        font-size: 0.75rem;
                        color: var(--text-muted);
                        display: flex;
                        align-items: center;
                    }}
                    
                    .embed-footer-icon {{
                        width: 20px;
                        height: 20px;
                        border-radius: 50%;
                        margin-right: 8px;
                    }}
                    
                    .mention {{
                        background-color: var(--mention-background);
                        color: var(--brand-color);
                        padding: 0 2px;
                        border-radius: 3px;
                    }}
                    
                    .footer {{
                        margin-top: 30px;
                        padding: 20px;
                        background-color: var(--background-tertiary);
                        border-radius: 8px;
                        text-align: left;
                    }}
                    
                    .participants-list {{
                        display: flex;
                        flex-direction: column;
                        flex-wrap: wrap;
                        justify-content: left;
                        gap: 10px;
                        margin-top: 10px;
                    }}
                    
                    .participant {{
                        display: flex;
                        align-items: center;
                        background-color: var(--background-secondary);
                        padding: 5px 10px;
                        border-radius: 4px;
                        max-width: fit-content;
                        box-sizing: border-box;
                    }}
                    
                    .participant-avatar {{
                        width: 20px;
                        height: 20px;
                        border-radius: 50%;
                        margin-right: 5px;
                    }}
                    .logo{{
                        padding-top: 5%;
                        width: 40%;
                    }}
                    .logo_div{{
                        float: right;
                        max-width: 20%;
                        justify-content: right;
                        align-items: right;
                        text-align: right;
                    }}
                    p{{
                        margin: 1px;
                    }}
                </style>
            </head>
            <body>
                <div class="discord-container">
                    <div class="header">
                        <div class="logo_div">
                            <img src="https://i.ibb.co/Xxy6kXfw/itiket-transparente.png" alt="itiket-transparente" border="0" class="logo"/>
                        </div>
                        <h1>Transcript del Ticket</h1>
                        <p><strong>Canal:</strong> {channel_name}</p>
                        <p><strong>Creado por:</strong> {creator.display_name} (ID: {creator.id})</p>
                        <p><strong>Generado el:</strong> {current_time.strftime(time_format)} (hora española)</p>
                    </div>"""

            async for message in interaction.channel.history(limit=None, oldest_first=True):
                participants.add(message.author)
                
                msg_time = message.created_at.astimezone(spain_tz)
                edited_time = message.edited_at.astimezone(spain_tz) if message.edited_at else None
                
                html_content += f"""
                <div class="message">
                    <img class="avatar" src="{message.author.display_avatar.url}" alt="{message.author.display_name}">
                    <div class="message-content">
                        <div class="message-header">
                            <span class="author">{message.author.display_name}</span>
                            <span class="timestamp">{msg_time.strftime(time_format)}</span>
                            {f'<span class="edited">(editado)</span>' if edited_time else ''}
                        </div>
                        <div class="message-body">"""

                # Contenido del mensaje
                if message.content:
                    # Procesar menciones
                    content = message.content
                    for mention in message.mentions:
                        content = content.replace(f'<@{mention.id}>', f'<span class="mention">@{mention.display_name}</span>')
                        content = content.replace(f'<@!{mention.id}>', f'<span class="mention">@{mention.display_name}</span>')
                    
                    html_content += f"<p>{content}</p>"
                
                # Adjuntos (imágenes y otros archivos)
                if message.attachments:
                    html_content += '<div class="attachments">'
                    for attachment in message.attachments:
                        if attachment.url.lower().endswith(('.png', '.jpg', '.jpeg', '.gif', '.webp')):
                            html_content += f'<a href="{attachment.url}" target="_blank"><img class="attachment-image" src="{attachment.url}" alt="{attachment.filename}"></a>'
                        else:
                            html_content += f'<a href="{attachment.url}" target="_blank" class="attachment-file">{attachment.filename}</a>'
                    html_content += '</div>'
                
                # Embeds
                if message.embeds:
                    for embed in message.embeds:
                        html_content += '<div class="embed">'
                        
                        # Contenido principal del embed
                        html_content += '<div class="embed-content">'
                        
                        # Color del borde izquierdo
                        border_color = f"hsl({embed.color}, 100%, 50%)" if embed.color else "var(--brand-color)"
                        html_content = html_content.replace('border-left: 4px solid;', f'border-left: 4px solid {border_color};')
                        
                        # Título
                        if embed.title:
                            html_content += f'<div class="embed-title">{embed.title}</div>'
                        
                        # Descripción
                        if embed.description:
                            html_content += f'<div class="embed-description">{embed.description}</div>'
                        
                        # Campos
                        if embed.fields:
                            html_content += '<div class="embed-fields">'
                            for field in embed.fields:
                                html_content += f'<div class="embed-field"><div class="embed-field-name">{field.name}</div>{field.value}</div>'
                            html_content += '</div>'
                        
                        # Footer
                        if embed.footer:
                            html_content += '<div class="embed-footer">'
                            if embed.footer.icon_url:
                                html_content += f'<img class="embed-footer-icon" src="{embed.footer.icon_url}" alt="Footer icon">'
                            html_content += f'<span>{embed.footer.text}</span>'
                            html_content += '</div>'
                        
                        html_content += '</div>'  # Cierre embed-content
                        
                        html_content += '</div>'  # Cierre embed
                
                html_content += "</div></div></div>"  # Cierres de message-body, message-content y message

            # Footer con participantes
            html_content += """

            <div class="footer">
                <div class="logo_div">
                    <img src="https://i.ibb.co/Xxy6kXfw/itiket-transparente.png" alt="itiket-transparente" border="0" class="logo"/>
                </div>
                <h2>Participantes</h2>
                <div class="participants-list">"""
            
            for participant in sorted(participants, key=lambda x: x.display_name):
                if participant == creator:
                    continue
                html_content += f"""
                    <div class="participant">
                        <img class="participant-avatar" src="{participant.display_avatar.url}" alt="{participant.display_name}">
                        <span>{participant.display_name} (ID: {participant.id})</span>
                    </div>"""
            
            html_content += """
                </div>
                <p style="margin-top: 20px;">Transcript generado automáticamente</p>
            </div>
            </div>
            </body>
            </html>"""

            # Guardar archivo HTML
            with open(filepath, 'w', encoding='utf-8') as f:
                f.write(html_content)

            category_name = interaction.channel.category.name.replace("Closed-", "")
            channel_name = interaction.channel.name.replace("🎫┇closed-", "")
            transcript_embed = discord.Embed(
                title=f"📝 Transcript de {category_name}",  # Usamos el nombre limpio
                description=f'{channel_name}',
                color=discord.Color.from_rgb(39, 118, 223),
                timestamp=current_time
            )

            files = []
            try:
                logo_file = discord.File("./cogs/banner/logo.png", filename="logo.png")
                transcript_embed.set_thumbnail(url="attachment://logo.png")
                transcript_embed.set_footer(
                    text=f"Ticket ID: {interaction.channel.id}",
                    icon_url="attachment://logo.png"
                )
                files.append(logo_file)
                banner_file = discord.File("./cogs/banner/standard.gif", filename="standard.gif")
                transcript_embed.set_image(url="attachment://standard.gif")
                files.append(banner_file)
            except Exception as e:
                print(f"[WARNING] No se pudieron cargar imágenes: {e}")

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

            await transcript_channel.send(
                embed=transcript_embed,
                files=files
            )

            await transcript_channel.send(
                file=discord.File(filepath),
            )
            
            return True
            
        except Exception as e:
            error_time = datetime.now().astimezone(spain_tz).strftime(time_format)
            print(f"[ERROR-TRANSCRIPT] {error_time} - Error:", e)
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

        if not interaction.channel.name.startswith("🎫┇closed-"):
            return await interaction.followup.send("⚠️ Ya está abierto", ephemeral=True, delete_after=5)

        config = TicketConfig().get_guild_config(interaction.guild.id)
        modrole = interaction.guild.get_role(config.get("modrol"))

        creator = None
        async for message in interaction.channel.history(limit=10, oldest_first=True):
            if message.author == self.bot.user and message.mentions:
                creator = message.mentions[0] 
                break

        closed_category = interaction.channel.category
        original_category_name = closed_category.name.replace("Closed-", "")
        original_category = utils.get(interaction.guild.categories, name=original_category_name)
        
        if not original_category:
            original_category = await interaction.guild.create_category(original_category_name)
            await original_category.set_permissions(
                interaction.guild.default_role,
                view_channel=False,
                send_messages=False
            )
            await original_category.set_permissions(
                modrole,
                view_channel=True,
                manage_channels=True,
                send_messages=True
            )

        overwrites = {
            interaction.guild.default_role: discord.PermissionOverwrite(view_channel=False),
            modrole: discord.PermissionOverwrite(
                view_channel=True,
                send_messages=True,
                manage_messages=True
            )
        }
        async for message in interaction.channel.history(limit=200):
            if not message.author.bot and message.author not in overwrites:
                overwrites[message.author] = discord.PermissionOverwrite(
                    view_channel=True,
                    send_messages=True, 
                    read_message_history=True
                )

        await interaction.channel.edit(
            name=f"🎫┇{interaction.channel.name.split('closed-')[-1]}",
            category=original_category, 
            overwrites=overwrites,
            reason=f"Reapertura por {interaction.user}"
        )

        await interaction.delete_original_response()

        if creator:
            temp_ping = await interaction.channel.send(f"{creator.mention}")
            await temp_ping.delete() 

        confirmed = discord.Embed(
            title='🔓 TICKET REABIERTO',
            description=f'El ticket ha sido reabierto por {interaction.user.mention}\n\nAhora puedes continuar con tu consulta.',
            color=discord.Color.green()
        )
        confirmed_m = await interaction.channel.send(embed=confirmed)
        await asyncio.sleep(3)
        await confirmed_m.delete()

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
            self.bot.add_view(HelpView())
            self.bot.add_view(TicketView(self.bot))
            self.bot.add_view(ConfirmClose(self.bot))
            self.bot.add_view(PostCloseActions(self.bot))
            self.persistent_views_added = True
            print("[Tickets] Vistas persistentes registradas")

    @commands.Cog.listener()
    async def on_ready(self):
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

            file = discord.File("./cogs/banner/standard.gif", filename="standard.gif")
            embed.set_image(url="attachment://standard.gif")
            
            await interaction.response.send_message(embed=embed, file=file, ephemeral=True)

            await asyncio.sleep(1)
            await interaction.delete_original_response()
            await asyncio.sleep(0.5)

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
