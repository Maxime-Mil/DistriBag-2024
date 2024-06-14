def generate_sigfox_frame(message):
    # Convertir le message en ASCII
    ascii_message = message.encode('ascii')

    # Convertir l'ASCII en hexadécimal
    hex_payload = ascii_message.hex()

    # Preamble (exemple: "8216")
    preamble = "8216"

    # End-device ID (exemple: "ABCDEF01")
    device_id = "ABCDEF01"

    # Construction de la trame complète
    sigfox_frame = preamble + device_id + hex_payload

    return sigfox_frame

# Fonction principale
def main():
    # Entrée du message
    message = input("Entrez votre message: ")

    # Générer la trame Sigfox
    frame = generate_sigfox_frame(message)

    # Afficher la trame générée
    print("Trame Sigfox générée:", frame)

if __name__ == "__main__":
    main()
