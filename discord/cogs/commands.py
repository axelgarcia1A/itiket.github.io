import discord, asyncio, json, os
from discord.ext import commands
from discord import app_commands
from typing import Optional

class Commands(commands.Cog):
    def __init__(self, bot):
        self.bot = bot
        self.role_file = "./cogs/json/role.json"
        os.makedirs(os.path.dirname(self.role_file), exist_ok=True)
        if not os.path.exists(self.role_file):
            with open(self.role_file, "w") as f:
                json.dump({}, f)

    @commands.Cog.listener()
    async def on_ready(self):
        print(f'{__name__} is ready.')

    async def get_buyer_role(self, guild: discord.Guild) -> Optional[discord.Role]:
        try:
            with open(self.role_file, "r") as f:
                data = json.load(f)
                guild_id = str(guild.id)
                if guild_id in data and data[guild_id].get("buyer"):
                    return guild.get_role(data[guild_id]["buyer"])
        except Exception as e:
            print(f"Error al obtener rol buyer: {e}")
        return None

    @app_commands.command(name='help', description='Comandos del bot')
    @app_commands.checks.has_permissions(manage_messages=True)
    async def help(self, interaction: discord.Interaction):
        await interaction.response.defer()
        embed = discord.Embed(
            title='Guía de comandos:',
            color=discord.Color.from_rgb(39, 118, 223)
        )
        embed.add_field(name='------MODERACIÓN------', value='', inline=False)
        embed.add_field(name='clear', value='Elimina el número de mensajes puestos', inline=False)
        embed.add_field(name='kick', value='Expulsa a un usuario del discord', inline=False)
        embed.add_field(name='ban', value='Banea a un usuario del discord', inline=False)
        embed.add_field(name='unban', value='Desbanea a un usuario de discord (requerido ID del user)', inline=False)
        embed.add_field(name='setup', value='Definir los roles automáticos (comprador y autorole al entrar)', inline=False)
        embed.add_field(name='------TICKETS------', value='', inline=False)
        embed.add_field(name='panel', value='Crea el panel de tickets', inline=False)
        embed.add_field(name='adduser', value='Añade a un usuario a un ticket', inline=False)
        embed.add_field(name='remuser', value='Elimina a un usuario a un ticket', inline=False)
        embed.add_field(name='addrol', value='Añade a un rol a un ticket', inline=False)
        embed.add_field(name='remrol', value='Elimina a un rol a un ticket', inline=False)
        
        file = discord.File("./cogs/banner/standard.gif", filename="standard.gif")
        embed.set_image(url="attachment://standard.gif")
        await interaction.followup.send(embed=embed, file=file, ephemeral=True)
    
    @app_commands.command(name='informacion', description='Revisa nuestros servicios')
    async def information(self, interaction: discord.Interaction):
        await interaction.response.defer()
        embed = discord.Embed(title='Información', color=discord.Color.from_rgb(39, 118, 223))
        embed.add_field(name='<:itiket:1352670742772318290> Web', value='Comprueba nuestra web clicando [aquí](https://www.itcket.cat)', inline=False)
        embed.add_field(name='<:discord:1352670613587755159> Discord', value='Para unirte a nuestro servidor de discord, haz click [aquí](https://discord.gg/mAFjh7cf5t)', inline=False)
        embed.add_field(name='<:telegram:1352670399485181972> Telegram', value='Para conectactar con nuestro bot de telegram, busca en la app "iTicket Telegram"', inline=False)
        file = discord.File("./cogs/banner/standard.gif", filename="standard.gif")
        embed.set_image(url="attachment://standard.gif")
        await interaction.followup.send(embed=embed, file=file)

    @app_commands.command(name='sell', description='Comando a poner cuando se realize un pago')
    @app_commands.checks.has_permissions(manage_messages=True)
    async def buy(self, interaction: discord.Interaction, user: discord.User):
        buyer_role = await self.get_buyer_role(interaction.guild)
        if not buyer_role:
            return await interaction.response.send_message(
                "❌ **Sistema no configurado**\n"
                "El rol de comprador no está configurado.\n"
                "Un administrador debe usar primero: `/setup type:user role:@Rol buyer:@RolComprador`",
                ephemeral=True
            )

        if not interaction.guild.me.guild_permissions.manage_roles:
            return await interaction.response.send_message(
                "❌ No tengo permisos para gestionar roles",
                ephemeral=True
            )

        if buyer_role.position >= interaction.guild.me.top_role.position:
            return await interaction.response.send_message(
                "❌ Mi rol está por debajo del rol de comprador\n"
                "Arrástrame más arriba en la lista de roles para poder asignarlo",
                ephemeral=True
            )

        await interaction.response.defer()
        await interaction.delete_original_response()
        message = await interaction.channel.send(user.mention)
        await message.delete()
        
        embed = discord.Embed(
            title='¡Pago Recibido!',   
            description=f'¡Muchas grácias {user.mention} por realizar la compra!\nEn breves momentos un miembro del equipo de desarrolladores te indicará los siguientes pasos.',
            color=discord.Color.from_rgb(39, 118, 223)
        )
        file = discord.File("./cogs/banner/standard.gif", filename="standard.gif")
        embed.set_image(url="attachment://standard.gif")
        await interaction.channel.send(embed=embed, file=file)

        await user.add_roles(buyer_role) 

async def setup(bot):
    await bot.add_cog(Commands(bot))