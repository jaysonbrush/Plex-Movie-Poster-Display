# Plex Movie Poster Display

A web-based display that shows the currently playing movie or TV show poster from your Plex Media Server. When nothing is playing, it displays random posters from your unwatched library.

> This is a fork of [MattsShack/Plex-Movie-Poster-Display](https://github.com/MattsShack/Plex-Movie-Poster-Display) with security improvements and code refactoring.

## Features

- Displays currently playing media poster from Plex
- Shows random unwatched movie posters when idle
- Customizable "Now Showing" and "Coming Soon" text overlays
- Support for custom fonts and colors
- Progress bar display option
- Background art with blur effect
- Web-based settings panel (press 's' to access)
- Mobile-friendly responsive design

## Security Improvements (This Fork)

- **Bcrypt password hashing** - Passwords are now securely hashed instead of stored in plaintext
- **Session timeout** - Auto-logout after 30 minutes of inactivity
- **CSRF protection** - Forms are protected against cross-site request forgery
- **Secure session handling** - Proper session initialization and destruction
- **Token validation** - Plex token checks before loading the display

## Installation

1. Clone this repository to your web server
2. Copy `config.php.example` to `config.php`
3. Edit `config.php` with your Plex server details and credentials
4. Access the application via your web browser
5. Press 's' to open the settings panel

## Configuration

Edit `config.php` with your settings:

- `$plexServer` - Your Plex server hostname or IP
- `$plexToken` - Your Plex authentication token ([How to find your token](https://support.plex.tv/articles/204059436-finding-an-authentication-token-x-plex-token/))
- `$plexClient` - IP address of the client device to monitor
- `$pmpUsername` / `$pmpPassword` - Login credentials for the settings panel

## Recommended Setup

Works great on a Raspberry Pi Zero W connected to a display via HDMI. Configure the Pi to:
1. Boot to desktop
2. Auto-start Chromium in kiosk mode
3. Load the Plex Movie Poster Display URL

The lightweight nature of this application makes it ideal for low-power devices like the Pi Zero W.

For a detailed guide on setting up your Raspberry Pi as a kiosk display, see: [Raspberry Pi Digital Signage](https://www.jaysonbrush.com/index.php/2022/08/07/raspberry-pi-digital-signage/)

## Credits

- Original project by [MattsShack](https://github.com/MattsShack/Plex-Movie-Poster-Display)
- Security enhancements and refactoring by [jaysonbrush](https://github.com/jaysonbrush)

## License

GPL-3.0 - See [LICENSE](LICENSE) for details.
