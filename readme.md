![YouTube Annotation Archive logo](https://i.imgur.com/Bq5AIyN.png)

![License](https://img.shields.io/badge/license-MIT-red.svg?style=flat)
![Version](https://img.shields.io/badge/version-v0.5--alpha-lightgrey.svg)

# YouTube Annotation Player

On January 15, 2019, YouTube will remove all annotations from videos hosted on their platform. This project is a couple of scripts that will enable you to play videos in your browser while supporting the playback of YouTube annotations. As of right now, this is mainly a proof of concept, but it's very useable in its current state.

**Official announcement:** https://support.google.com/youtube/answer/7342737

**Archived link #1:** https://web.archive.org/web/20181201095154/https://support.google.com/youtube/answer/7342737

**Archived link #2:** http://archive.vn/6saFC

## Requirements
- [PHP 5.4.0](http://php.net/) or higher (the built-in web server is fine to use)

- A modern web browser: [Mozilla Firefox 64](https://www.mozilla.org/en-US/firefox/new/?redirect_source=firefox-com) or [Google Chrome 70](https://www.google.com/chrome/) are recommended

## How to use
If you're on Windows, you can get the player over on the [Releases](https://github.com/Seirade/YouTube-Annotation-Player/releases) page.

There will be a bare-minimum copy of PHP bundled with it and a batch file to launch the server for you. Simply double-click the `php.bat` and nagivate your browser to http://127.0.0.1:1337/player.php. Once you're on the page, type the ID of any video you have downloaded to the `Videos` directory. Partial name searches technically also work, but were not a planned feature. If you go that route, you'll experience a page reload upon clicking a timecoded annotation for the same video (but only for the first time). Bear in mind, this is simply a proof of concept project meant to be later developed into something more complete and user-friendly.

## Known issues
- Close button placement is not ideal for very small annotations near the edges of the player. See: [Annotation Tetris](https://www.youtube.com/watch?v=eIIV6a2Pdh4)
- Resizing the browser/player currently does not adjust annotations
- Some annotations show properties not described in the XML. See: [First Collaborative Art Project using YouTube Annotations](https://www.youtube.com/watch?v=XwxBJEzgqWU) annotation ID: `annotation_199415`, the black 1px border when hovering
- The link icons in highlights are smaller than they should be, and not properly aligned

## To-Do
- [ ] Implement speech bubble annotations using SVG shapes
- [ ] Implement label annotations
- [ ] Add hover text for highlights
- [ ] Recreate pause annotations?

## Adding videos
The player will assume you have the following directory structure at the root of the project:
```
Videos
└─── Uploader name
     └─── Video title [id]
          ├─── Video file [id].(mp4, webm, etc)
          └─── Annotation.xml
```
The only important bits are that each video has its own separete folder with the ID in the name, and that they be placed in the `Videos` directory. Beyond that, you can organize however you like, such as by uploader/channel name (or ID), upload date, or whatever else. For downloading videos I recommend using [youtube-dl](https://rg3.github.io/youtube-dl/) to archive as many annotations XML files as possible. Here's the config file that I use:
```
--skip-download
--write-description
--write-info-json
--write-annotations
--all-subs
--no-overwrites
--ignore-errors
--no-check-certificate
--datebefore 20170503
--output "Videos/%(uploader)s/%(title)s [%(id)s]/%(id)s.%(ext)s"
```
- `--skip-download` Remove this option if you need to download the video
- `--datebefore 20170503` YouTube discontinued the annotation editor on May 2, 2017, so this will skip any videos uploaded after that date. I added an extra day just to be safe

:mag: Need a tool for downloading the unlisted pieces of interactive videos? I made a tool just for that! Check out the [Youtube Interactive Video Crawler](https://github.com/Seirade/YouTube-Interactive-Video-Crawler)

## Contributing
For the purposes of this player, I'm only looking to match YouTube's annotations as close to pixel-perfect as possible, while still keeping the codebase lightweight. However, it is far more important to archive annotations while they're still available! If you'd like to lend a hand, it's very easy, and you don't have to be a programmer. For that, I've set up a Discord server where our efforts can be concentrated and in real time. Please join with this link: https://discord.gg/HcjZKdR