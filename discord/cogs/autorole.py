import discord
import json
import os
from discord.ext import commands
from discord import app_commands
from typing import Optional

class AutoRole(commands.Cog):
    def __init__(self, bot):
        self.bot = bot
        self.role_file = "./cogs/json/role.json"
        os.makedirs(os.path.dirname(self.role_file), exist_ok=True)
        if not os.path.exists(self.role_file):
            with open(self.role_file, "w") as f:
                json.dump({}, f)

    @commands.Cog.listener()
    async def on_ready(self):
        print(f'{self.__class__.__name__} is ready.')
    
    @app_commands.command(name="setup", description="Configura los roles automáticos")
    @app_commands.describe(
        type="Tipo de rol (user o bot)",
        role="Rol a asignar automáticamente",
        buyer="Rol para compradores (opcional)"
    )
    @app_commands.choices(type=[
        app_commands.Choice(name="User", value="user"),
        app_commands.Choice(name="Bot", value="bot"),
    ])
    @app_commands.checks.has_permissions(manage_roles=True)
    async def setup_autorole(self, interaction: discord.Interaction, 
                           type: str, 
                           role: discord.Role, 
                           buyer: Optional[discord.Role] = None):
        try:
            if not interaction.guild.me.guild_permissions.manage_roles:
                return await interaction.response.send_message(
                    "❌ Necesito el permiso 'Gestionar roles' para esto",
                    ephemeral=True
                )

            if role.position >= interaction.guild.me.top_role.position:
                return await interaction.response.send_message(
                    "❌ El rol debe estar por debajo de mi rol más alto",
                    ephemeral=True
                )

            with open(self.role_file, "r") as f:
                data = json.load(f)

            guild_id = str(interaction.guild.id)
            if guild_id not in data:
                data[guild_id] = {"auto_role_u": None, "auto_role_b": None, "buyer": None}

            if type == "user":
                data[guild_id]["auto_role_u"] = role.id
            elif type == "bot":
                data[guild_id]["auto_role_b"] = role.id

            if buyer is not None:
                if buyer.position >= interaction.guild.me.top_role.position:
                    return await interaction.response.send_message(
                        "❌ El rol de comprador debe estar por debajo de mi rol",
                        ephemeral=True
                    )
                data[guild_id]["buyer"] = buyer.id

            with open(self.role_file, "w") as f:
                json.dump(data, f, indent=4)
            response_msg = discord.Embed(
                title='ROLES CONFIGURADOS CORRECTAMENTE',
                description=f"Rol para {type}s: {role.mention}\nRol de comprador: {buyer.mention if buyer else 'No modificado'}",
                color=discord.Color.from_rgb(39, 118, 223)
            )
            file = discord.File("./cogs/banner/standard.gif", filename="standard.gif")
            response_msg.set_image(url="attachment://standard.gif")
            await interaction.response.send_message(embed=response_msg, file=file, ephemeral=True)

        except Exception as e:
            print(f"[ERROR] En setup_autorole: {str(e)}")
            await interaction.response.send_message(
                f"❌ Error crítico: {str(e)}",
                ephemeral=True
            )

    @commands.Cog.listener()
    async def on_member_join(self, member):
        try:
            with open(self.role_file, "r") as f:
                data = json.load(f)
                guild_id = str(member.guild.id)
                
                if guild_id in data:
                    role_key = "auto_role_b" if member.bot else "auto_role_u"
                    if role_id := data[guild_id].get(role_key):
                        if role := member.guild.get_role(role_id):
                            await member.add_roles(role)
        except Exception as e:
            print(f"Error en on_member_join: {e}")

async def setup(bot):
    await bot.add_cog(AutoRole(bot))