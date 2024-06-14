#include <iostream>
#include <bitset>
#include <libserialport.h>
#include <cstring>
#include <unordered_map>

using namespace std;

const int MAX_FRAME_SIZE = 1; // Taille maximale de la trame (en octets)

// Fonction pour initialiser la carte des pièces
unordered_map<int, double> initializeCoinMap() {
    unordered_map<int, double> map;
    map[40] = 2.0;
    map[20] = 1.0;
    map[10] = 0.5;
    map[4] = 0.2;
    map[2] = 0.1;
    map[1] = 0.05;
    return map;
}

// Carte globale des pièces
unordered_map<int, double> coinMap = initializeCoinMap();

void checkAndHandleError(enum sp_return result, const char* message) {
    if (result != SP_OK) {
        cerr << message << ": " << sp_last_error_message() << endl;
        sp_free_error_message(sp_last_error_message());
        exit(EXIT_FAILURE);
    }
}

void configureSerialPort(struct sp_port* port) {
    enum sp_return result;

    // Définir le débit en bauds
    result = sp_set_baudrate(port, 9600);
    checkAndHandleError(result, "Erreur lors du réglage du débit en bauds");

    // Définir 8 bits de données
    result = sp_set_bits(port, 8);
    checkAndHandleError(result, "Erreur lors du réglage des bits de données");

    // Définir 1 bit d'arrêt
    result = sp_set_stopbits(port, 1);
    checkAndHandleError(result, "Erreur lors du réglage des bits d'arrêt");

    // Définir la parité paire
    /* result = sp_set_parity(port, SP_PARITY_EVEN);
    checkAndHandleError(result, "Erreur lors du réglage de la parité"); */

    // Définir le contrôle de flux sur aucun
    result = sp_set_flowcontrol(port, SP_FLOWCONTROL_NONE);
    checkAndHandleError(result, "Erreur lors du réglage du contrôle de flux");
}

void readFrame(struct sp_port* port, double &totalAmount, const double prixBaguette) {
    while (true) {
        // Tampon pour stocker l'octet entrant
        unsigned char byte;
        int bytesRead = sp_blocking_read(port, &byte, 1, 1000); // Délai d'attente d'1 seconde

        if (bytesRead > 0) {
            // Vérifier si l'octet reçu est constitué uniquement de uns
            if (byte == 0xFF) {
                // Ignorer l'impression de cet octet
                continue;
            }

            // Imprimer les données de l'octet reçu
            cout << "Données reçues : " << bitset<8>{byte} << " (" << static_cast<int>(byte) << ")" << endl;

            // Vérifier si l'octet correspond à une valeur dans la carte
            auto it = coinMap.find(static_cast<int>(byte));
            if (it != coinMap.end()) {
                totalAmount += it->second;
                cout << "Pièce insérée : " << it->first << ", Montant total : " << totalAmount << "€"<< endl;
                
                // Vérifier si le montant total est suffisant pour acheter une baguette
                if (totalAmount == prixBaguette) {
                    cout << "Baguette achetée ! Bonne journée !" << endl;
                    totalAmount = 0.0; // Réinitialiser le montant total
                } else if (totalAmount > prixBaguette) {
                    double change = totalAmount - prixBaguette;
                    cout << "Baguette achetée ! Merci ! Voici votre monnaie : " << change << "€" << endl;
                    totalAmount = 0.0; // Réinitialiser le montant total
                }

                // Afficher le montant restant à payer uniquement s'il est positif
                double restantAPayer = prixBaguette - totalAmount;
                if (restantAPayer >= 0 && restantAPayer != prixBaguette) {
                    cout << "Montant restant à payer : " << restantAPayer << "€" << endl;
                }
            } else {
                cout << "Pièce non reconnue : " << static_cast<int>(byte) << endl;
            }
        } else if (bytesRead < 0) {
            cerr << "Erreur lors de la lecture sur le port série : " << sp_last_error_message() << endl;
            sp_free_error_message(sp_last_error_message());
        } else {
            continue;
        }
    }
}

int main() {
    struct sp_port *port;
    double totalAmount = 0.0;
    const double prixBaguette = 1.0; // Prix d'une baguette

    // Message d'accueil indiquant le prix de la baguette
    cout << "Bienvenue ! Le prix d'une baguette est de " << prixBaguette << "€." << endl;

    // Liste tous les ports série disponibles
    struct sp_port **ports;
    sp_list_ports(&ports);

   /* for (int i = 0; ports[i]; i++) {
        cout << "Port trouvé : " << sp_get_port_name(ports[i]) << endl;
    } */

    // Libérer la liste des ports
    sp_free_port_list(ports);

    // Ouvrir le port série
    if (sp_get_port_by_name("/dev/ttyS0", &port) != SP_OK) {
        cerr << "Impossible de trouver le périphérique série : " << sp_last_error_message() << endl;
        sp_free_error_message(sp_last_error_message());
        return 1;
    }

    if (sp_open(port, SP_MODE_READ_WRITE) != SP_OK) {
        cerr << "Impossible d'ouvrir le périphérique série : " << sp_last_error_message() << endl;
        sp_free_error_message(sp_last_error_message());
        sp_free_port(port);
        return 1;
    }

    // Configurer le port série avec les paramètres corrects
    configureSerialPort(port);

    try {
        readFrame(port, totalAmount, prixBaguette);
    } catch (const std::exception &e) {
        cerr << "Exception : " << e.what() << endl;
    }

    // Fermer le port série
    sp_close(port);
    sp_free_port(port);
    return 0;
}

