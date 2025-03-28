import discord
import os
import easy_pil
import random
from discord.ext import commands

class JoinCard(commands.Cog):
    def __init__(self, c):
        self.c = c
        
    @commands.Cog.listener()
    async def on_ready(self):
        print(f'{__name__} is online')

    @commands.Cog.listener()
    async def on_member_join(self, member: discord.Member):
        try:
            welcome_channel = member.guild.system_channel
            if welcome_channel is None:
                return  # Exit if there is no system channel

            images = [image for image in os.listdir("./cogs/welcome_images") if image.endswith(('.png', '.jpg', '.jpeg'))]
            if not images:
                print("No images found in the welcome_images directory.")
                return  # Exit if there are no images

            random_image = random.choice(images)
            bg = easy_pil.Editor(f"./cogs/welcome_images/{random_image}").resize((1920, 1080))

            # Handle avatar
            avatar_url = str(member.avatar.url) if member.avatar else str(member.default_avatar.url)
            avatar_image = await easy_pil.load_image_async(avatar_url)
            avatar = easy_pil.Editor(avatar_image).resize((250, 250)).circle_image()

            font_big = easy_pil.Font.poppins(size=90, variant="bold")

            bg.paste(avatar, (835, 340))
            bg.ellipse((835, 340), 250, 250, outline=(39, 118, 223), stroke_width=15)

            # Outline parameters
            outline_color = (189, 193, 198)  # White outline
            stroke_width = 8  # Thickness of the outline

            for x_offset in [-stroke_width, 0, stroke_width]:
                for y_offset in [-stroke_width, 0, stroke_width]:
                    if x_offset != 0 or y_offset != 0:  # Skip the center position
                        bg.text((960 + x_offset, 620 + y_offset), f'¡Bienvenid@ a {member.guild.name}!', font=font_big, align="center", color=outline_color)

            # Now draw the main text on top
            bg.text((960, 620), f'¡Bienvenid@ a {member.guild.name}!', font=font_big, align="center", color=(39, 118, 223))


            img_file = discord.File(fp=bg.image_bytes, filename=f"welcome_{member.id}.png")
            await welcome_channel.send(f'¡Bienvenid@ a {member.guild.name} {member.mention}!')
            await welcome_channel.send(file=img_file)
        except Exception as e:
            print('Error', e)

async def setup(c):
    await c.add_cog(JoinCard(c))