import discord, asyncio
from discord.ext import commands
from discord import app_commands

class Commands(commands.Cog):
    def __init__(self, bot):
        self.bot = bot

    @commands.Cog.listener()
    async def on_ready(self):
        print(f'{__name__} is ready.')

    @app_commands.command(name='help', description='Comandos del bot')
    @app_commands.checks.has_permissions(manage_messages=True)
    async def help(self, interaction: discord.Interaction):
        embed = discord.Embed(
            title='Guía de comandos:',
            color=discord.Color.from_rgb(39, 118, 223)  # Color movido aquí
        )
        embed.add_field(name='------MODERACIÓN------', value='', inline=False)
        embed.add_field(name='clear', value='Elimina el número de mensajes puestos', inline=False)
        embed.add_field(name='kick', value='Expulsa a un usuario del discord', inline=False)
        embed.add_field(name='ban', value='Banea a un usuario del discord', inline=False)
        embed.add_field(name='unban', value='Desbanea a un usuario de discord (requerido ID del user)', inline=False)
        embed.add_field(name='------TICKETS------', value='', inline=False)
        embed.add_field(name='ticket', value='Crea el panel de tickets', inline=False)
        embed.add_field(name='adduser', value='Añade a un usuario a un ticket', inline=False)
        embed.add_field(name='remuser', value='Elimina a un usuario a un ticket', inline=False)
        embed.add_field(name='addrol', value='Añade a un rol a un ticket', inline=False)
        embed.add_field(name='remrol', value='Elimina a un rol a un ticket', inline=False)
        
        file = discord.File("./cogs/banner/standard.gif", filename="standard.gif")
        embed.set_image(url="attachment://standard.gif")
        await interaction.response.send_message(embed=embed, file=file, ephemeral=True)
    
    @app_commands.command(name='informacion', description='Revisa nuestros servicios')
    async def information(self, interaction: discord.Interaction):
        embed = discord.Embed(title='Información', color=discord.Color.from_rgb(39, 118, 223))
        embed.add_field(name='<:itiket:1352670742772318290> Web', value='Comprueba nuestra web clicando [aquí](https://www.itcket.cat)', inline=False)
        embed.add_field(name='<:discord:1352670613587755159> Discord', value='Para unirte a nuestro servidor de discord, haz click [aquí](https://discord.gg/mAFjh7cf5t)', inline=False)
        embed.add_field(name='<:telegram:1352670399485181972> Telegram', value='Para conectactar con nuestro bot de telegram, busca en la app "iTicket Telegram"', inline=False)
        file = discord.File("./cogs/banner/standard.gif", filename="standard.gif")
        embed.set_image(url="attachment://standard.gif")
        await interaction.response.send_message(embed=embed, file=file)



async def setup(bot):
    await bot.add_cog(Commands(bot))