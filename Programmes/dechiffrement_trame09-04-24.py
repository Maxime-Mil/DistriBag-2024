def decode_sigfox_frame(frame):
    # Décoder les champs de la trame
    preamble = frame[:4]
    device_id = frame[4:12]
    payload_hex = frame[12:]

    # Convertir le payload hexadécimal en ASCII
    payload_ascii = bytes.fromhex(payload_hex).decode('ascii')

    # Retourner les champs décodés
    return {
        "Preamble": preamble,
        "End-device ID": device_id,
        "Payload": payload_ascii
    }

# Fonction principale
def main():
    # Entrée de la trame Sigfox
    frame = input("Entrez la trame Sigfox (sans espaces): ")

    # Décoder la trame Sigfox
    decoded_frame = decode_sigfox_frame(frame)

    # Afficher les champs décodés
    print("Champs décodés de la trame Sigfox:")
    for key, value in decoded_frame.items():
        print(f"{key}: {value}")

if __name__ == "__main__":
    main()
