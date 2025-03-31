import discord, os, asyncio, json
from dotenv import load_dotenv
from itertools import cycle
from discord.ext import commands, tasks


bot = commands.Bot(command_prefix="!", intents=discord.Intents.all())

@tasks.loop(seconds=20)
async def change_status():
     await bot.change_presence(activity=discord.CustomActivity(name='👷 Building...'))

load_dotenv("./ENV/token.env")
token = os.getenv("token")

@bot.event
async def on_ready():
    print(f"Logged in as {bot.user}")
    change_status.start()
    try:
        synced_commands = await bot.tree.sync()
        print(f'Synced {len(synced_commands)} commands')
    except Exception as e:
         print('An error with syncing applicatiom commands has ocurred ',e)

@bot.event
async def on_guild_join(guild):
    with open ('./cogs/json/role.json', "r") as a:
        guilds = json.load(a)

        guilds.update(
             {
                  str(guild.id): {
                      "auto_role_u": None,
                      "auto_role_b": None,
                  }
             }
        )
    with open('./cogs/json/role.json', 'w') as q:
        json.dump(guilds, a, indent=4)
@bot.event
async def on_guild_remove(guild):
    with open ('./cogs/json/role.json', "r") as f:
        guilds = json.load(f)

        guilds.pop(str(guild.id), None)
    with open('./cogs/json/role.json', 'w') as i:
        json.dump(guilds, f, indent=4)

@bot.event
async def on_guild_join(guild):
    with open ('./cogs/json/ticket.json', "r") as a:
        guilds = json.load(a)

        guilds.update(
             {
                  str(guild.id): {
                      "modrol": None,
                      "transcript_id": None,
                  }
             }
        )
    with open('./cogs/json/ticket.json', 'w') as q:
        json.dump(guilds, a, indent=4)
@bot.event
async def on_guild_remove(guild):
    with open ('./cogs/json/ticket.json', "r") as f:
        guilds = json.load(f)

        guilds.pop(str(guild.id), None)
    with open('./cogs/json/ticket.json', 'w') as i:
        json.dump(guilds, f, indent=4)


async def load():
    for filename in os.listdir('./cogs'):
        if filename.endswith('.py'):
            await bot.load_extension(f'cogs.{filename[:-3]}')


async def main():
    async with bot:
            await load()
            await bot.start(token)

asyncio.run(main())