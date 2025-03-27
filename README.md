# Sandboss bypass with Profiles (macOS)
PoC presented at SOCON-2025 demonstrating the ability to bypass Office documents sandbox using specifically crafted macro 

This project presents an alternative to sandbox bypass under the Microsoft Office context. As previously presented at SOCON-2025, this macro prompts the user into accepting the installation of a malicious MacOS profile (.mobileconfig) using native Schema handlers.

Since opening the profile only alerts the users of a new profile ready for installation, we need to force open the Device Management interface to present the profile.

<img width="201" alt="image" src="https://github.com/user-attachments/assets/a22fd868-9b63-4474-99f7-639e795b7bcb" />

<img width="314" alt="image" src="https://github.com/user-attachments/assets/5ff2223a-7569-4acc-a923-2e4e5f284120" />

The macro forces the user to accept the installation in order to view the hidden content of the document, while automatically deleting the first (blocking) page.

## Components

### Macro

The macro simply defines the bypass strategy by download the profile from a remote server, opening (triggering the Profile Alert popup) and popping the Device Management interface. It loops until the user finishes installation. In order to verify the installation, _monitor.py_ constantly checks for new connection IPs. Since the machine will be proxied, it will inevitably register its IP in the server.

The solution for checking a successful installation can be vastly improved. **I'll leave that as an exercise for the reader**.

### monitor.py

Checks any new connections through the proxy.

###.htaccess
Since Firefox uses its own Certificate Trust chain, new certificates need to be defined directly into the app, which renders adding a certificate to the keychain useless. In order to avoid completely breaking the user's Firefox (and getting reported), the htaccess blocks any requests from Firefox to grab the PAC. Since it is not possible to grab it and we have the Fallback option in the .mobileconfig, it will not be proxied.

### PAC

This file needs to be hosted in a reachable server and is specified in the .mobileconfig. It defines rules for either using the proxy or not. Since Firefox uses its own Certificate Trust chain, new certificates need to be defined directly into the app, which renders adding a certificate to the keychain useless. In order to avoid completely breaking the user's Firefox (and getting reported), the PAC verifies the user agent of the connection, if it is coming from a Firefox browser, it will simply pass-through without using the proxy.

### MobileConfig

This is the profile configuration. You should be able to customize almost every field, except ProxyCaptiveLoginAllowed and ProxyType, otherwise your PAC verification might break. Make sure to specify your PAC url and your mitmproxy certificate to be installed in the target's Keychain.

### certgen.sh

Generates your certificate and automatically replaces in mitm folder. Just make sure to edit cert_gen.cnf according to your preferences and run base64 certs/ca.crt to obtain the base64 payload to be added to the .mobileconfig.

### monitor.py

This should be started with your mitmproxy in order to monitor new proxy connections.

## IMPORTANT

This technique has been fixed by Apple. New certificates installed with profiles (.mobileconfig) do not have SSL Trust. These need to be manually granted in the Keychain. **You can still abuse this technique with other profiles (e.g. https://github.com/MythicAgents/orthrus)**

## Steps

1. Setup your remote server, DNS and SSL certificates
2. Modify the pac file to add your server. It should already have the Firefox exclusion (so it doesn't break)
3. Customize cert_gen.cnf place in the same directory as certgen.sh in your remote server.
4. Store the .htaccess in your remote file. Customize it accordingly.
5. In your remote server:

`apt install mitmproxy`

`./cergen.sh`

`base64 certs/ca.crt`

5. Copy the base64 value and add it to .mobileconfig. (PLACE_YOUR_MITM_BASE64_CERTIFICATE_HERE)
6. Modify the pac url in the mobileconfig pointing to where your pac is hosted.
7. Place monitor.py in your remote server and start.
8. Place monitor.php in your remote server and make it accessible.
9. Change the macro accordingly to point to the correct urls of where the artifacts are located.
10. mitmproxy --listen-host 0.0.0.0 --listen-port 8080 --set block_global=false
11. Profit
