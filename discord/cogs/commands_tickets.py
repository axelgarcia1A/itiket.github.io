import discord
from discord.ext import commands
from discord import app_commands

class CommandsT(commands.Cog):
    def __init__(self, bot):
        self.bot = bot

    @commands.Cog.listener()
    async def on_ready(self):
        print(f'{__name__} is ready.')
        
    @app_commands.command(name="adduser", description="Añade un usuario a un ticket")
    @app_commands.describe(member="El usuario que deseas añadir al ticket")
    @app_commands.default_permissions(manage_guild = True)
    @app_commands.checks.has_permissions(manage_messages=True)
    @app_commands.checks.bot_has_permissions(manage_channels = True)
    async def add_user(self, interaction: discord.Interaction, member: discord.Member):
        user_bot = 1325890689468338237
        try:
            if interaction.channel.name.startswith("🎫┇s") or interaction.channel.name.startswith("🎫┇d") or interaction.channel.name.startswith("🎫┇a"):
                if member.id != user_bot:
                    await interaction.channel.set_permissions(member, view_channel=True, send_messages=True)
                    await interaction.response.send_message(f"{member.mention} ha sido añadido al ticket.", ephemeral=True)
                else:
                    await interaction.response.send_message('No puedes añadir el usuario del bot', ephemeral=True)
                    return
            else:
                await interaction.response.send_message("Este comando solo puede usarse en un ticket.", ephemeral=True)
        except Exception as e:
            await interaction.response.send_message(f"❌ Error: {str(e)}", ephemeral=True)

    @app_commands.command(name="remuser", description="Elimina un usuario a un ticket")
    @app_commands.default_permissions(manage_guild = True)
    @app_commands.describe(member="El usuario que deseas eliminar del ticket")
    @app_commands.checks.has_permissions(manage_messages=True)
    @app_commands.checks.bot_has_permissions(manage_channels = True)
    async def rem_user(self, interaction: discord.Interaction, member: discord.Member):
        user_bot = 1325890689468338237
        try:
            if interaction.channel.name.startswith("🎫┇s") or interaction.channel.name.startswith("🎫┇d") or interaction.channel.name.startswith("🎫┇a"):
                if member.id != user_bot:
                    await interaction.channel.set_permissions(member, view_channel=False, send_messages=False)
                    await interaction.response.send_message(f"{member.mention} ha sido eliminado al ticket.", ephemeral=True)
                else:
                    await interaction.response.send_message('No puedes eliminar el usuario del bot', ephemeral=True)
                    return
            else:
                await interaction.response.send_message("Este comando solo puede usarse en un ticket.", ephemeral=True)
        except Exception as e:
            await interaction.response.send_message(f"❌ Error: {str(e)}", ephemeral=True)
            print(f"Error en rem_user: {e}")
    
    @app_commands.command(name="addrole", description="Añade un rol a un ticket")
    @app_commands.describe(role="El rol que deseas añadir al ticket")
    @app_commands.default_permissions(manage_guild = True)
    @app_commands.checks.has_permissions(manage_messages=True)
    @app_commands.checks.bot_has_permissions(manage_channels = True)
    async def add_rol(self, interaction: discord.Interaction, role:discord.Role):
        rol_bot = interaction.guild.get_role(1325922428794306715)
        try:
            if interaction.channel.name.startswith("🎫┇s") or interaction.channel.name.startswith("🎫┇d") or interaction.channel.name.startswith("🎫┇a"):
                if role != rol_bot:
                    await interaction.channel.set_permissions(role, view_channel=True, send_messages=True)
                    await interaction.response.send_message(f"{role.mention} ha sido añadido al ticket.", ephemeral=True)
                else:
                    await interaction.followup.send('No puedes añadir el rol del bot', ephemeral=True)
                    return
            else:
                await interaction.response.send_message("Este comando solo puede usarse en un ticket.", ephemeral=True)
        except Exception as e:
            await interaction.response.send_message(f"❌ Error: {str(e)}", ephemeral=True)

    @app_commands.command(name="remrole", description="Elimina un usuario a un ticket")
    @app_commands.default_permissions(manage_guild = True)
    @app_commands.describe(role="El usuario que deseas eliminar del ticket")
    @app_commands.checks.has_permissions(manage_messages=True)
    @app_commands.checks.bot_has_permissions(manage_channels = True)
    async def rem_rol(self, interaction: discord.Interaction, role:discord.Role):
        rol_bot = interaction.guild.get_role(1325922428794306715)
        try:
            if interaction.channel.name.startswith("🎫┇s") or interaction.channel.name.startswith("🎫┇d") or interaction.channel.name.startswith("🎫┇a"):
                if role != rol_bot:
                    await interaction.channel.set_permissions(role, view_channel=False, send_messages=False)
                    await interaction.response.send_message(f"{role.mention} ha sido eliminado al ticket.", ephemeral=True)
                else:
                    await interaction.followup.send('No puedes eliminar el rol del bot', ephemeral=True)
                    return
            else:
                await interaction.response.send_message("Este comando solo puede usarse en un ticket.", ephemeral=True)
        except Exception as e:
            await interaction.response.send_message(f"❌ Error: {str(e)}", ephemeral=True)
            print(f"Error en rem_user: {e}")
async def setup(bot):
    await bot.add_cog(CommandsT(bot))