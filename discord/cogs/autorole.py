import discord, json
import os
from discord.ext import commands
from discord import app_commands

class AutoRole(commands.Cog):
    def __init__(self, bot):
        self.bot = bot
        # Asegurar que el directorio y archivo existan
        os.makedirs("./cogs/json", exist_ok=True)
        if not os.path.exists("./cogs/json/role.json"):
            with open("./cogs/json/role.json", "w") as f:
                json.dump({}, f)

    @commands.Cog.listener()
    async def on_ready(self):
        print(f'{__name__} is ready.')
    
    @app_commands.command(name="setup", description="Configura el rol automático al unirse")
    @app_commands.describe(type="Tipo de rol", role="Rol a asignar")
    @app_commands.checks.has_permissions(administrator=True)
    @app_commands.choices(type=[
        app_commands.Choice(name="user", value="user"),
        app_commands.Choice(name="bot", value="bot"),
    ])
    async def setup_autorole(self, interaction: discord.Interaction, type: str, role: discord.Role):
        try:
            with open("./cogs/json/role.json", "r") as f:
                data = json.load(f)       
            guild_id = str(interaction.guild.id)
            if guild_id not in data:
                data[guild_id] = {"auto_role_u": None, "auto_role_b": None}
            if type == "user":
                data[guild_id]["auto_role_u"] = role.id
            elif type == "bot":
                data[guild_id]["auto_role_b"] = role.id
            with open("./cogs/json/role.json", "w") as f:
                json.dump(data, f, indent=4) 
            await interaction.response.send_message(
                f"✅ Rol {role.mention} configurado para {type}s.",
                ephemeral=True
            )
        except Exception as e:
            await interaction.response.send_message(f"❌ Error: {str(e)}", ephemeral=True)
            print(f"Error en setup_autorole: {e}")

    @commands.Cog.listener()
    async def on_member_join(self, member):
        try:
            with open("./cogs/json/role.json", "r") as f:
                data = json.load(f)
                guild_id = str(member.guild.id)
                if member.bot:
                    role_key = "auto_role_b"
                else:
                    role_key = "auto_role_u"
                
                if guild_id in data and data[guild_id][role_key]:
                    role = member.guild.get_role(data[guild_id][role_key])
                    if role:
                        await member.add_roles(role) 
        except Exception as e:
            print(f"Error en on_member_join: {e}")

async def setup(bot):
    await bot.add_cog(AutoRole(bot))