import discord, asyncio
from discord.ext import commands
from discord import app_commands

class Mod(commands.Cog):
    def __init__(self, bot):
        self.bot = bot

    @commands.Cog.listener()
    async def on_ready(self):
        print(f'{__name__} is ready.')

    @app_commands.command(name="clear", description="Elimina un número especifico de mensajes del canal")
    @app_commands.checks.has_permissions(manage_messages=True)
    async def delete_messages(self, interaction: discord.Interaction, amount: int):
        await interaction.response.defer(ephemeral=True)
        if amount < 1:
            await interaction.followup.send(f"{interaction.user.mention}, especifica una cantidad superior a uno", ephemeral=True)
            return
        deleted_messages = await interaction.channel.purge(limit=amount)
        await asyncio.sleep(0,5)
        await interaction.followup.send(f"{interaction.user.mention} ha eliminado {len(deleted_messages)} mensaje(s).",ephemeral=True)
        await asyncio.sleep(1)
        await interaction.delete_original_response()

    @app_commands.command(name="kick", description="Expulsa a un miembro especificado")
    @app_commands.checks.has_permissions(kick_members=True)
    async def kick(self, interaction: discord.Interaction, member: discord.Member):
        user_bot = 1325890689468338237
        if member.id != user_bot:
            await interaction.guild.kick(member)
            await interaction.response.send_message(f"Realizado! Has expulsado a {member.mention}!", ephemeral=True)
        else:
            await interaction.response.send_message('No puedes expulsar el bot', ephemeral=True)
            return

    @app_commands.command(name="ban", description="Bannea a un miembro especifico")
    @app_commands.checks.has_permissions(ban_members=True)
    async def ban(self, interaction: discord.Interaction, member: discord.Member):
        user_bot = 1325890689468338237
        if member.id != user_bot:
            await interaction.guild.ban(member)
            await interaction.response.send_message(f"Realizado! Has baneado a {member.mention}!", ephemeral=True)
        else:
            await interaction.response.send_message('No puedes bannear el bot', ephemeral=True)
            return

    @app_commands.command(name="unban", description="Desbannea a un usuario especificado por su user ID.")
    @app_commands.checks.has_permissions(ban_members=True)
    async def unban(self, interaction: discord.Interaction, user_id: str):
        user = await self.bot.fetch_user(user_id)
        await interaction.guild.unban(user)
        await interaction.response.send_message(f"Realizado! Has desbanneado a {user.mention}!", ephemeral=True)

async def setup(bot):
    await bot.add_cog(Mod(bot))