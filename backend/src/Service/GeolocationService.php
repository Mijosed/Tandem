<?php

namespace App\Service;

use Psr\Log\LoggerInterface;

class GeolocationService
{
    private LoggerInterface $logger;
    
    // Mapping de quelques codes postaux vers codes INSEE (on peut l'étendre)
    private array $postalToInsee = [
        // Paris
        '75001' => '75101', '75002' => '75102', '75003' => '75103', '75004' => '75104',
        '75005' => '75105', '75006' => '75106', '75007' => '75107', '75008' => '75108',
        '75009' => '75109', '75010' => '75110', '75011' => '75111', '75012' => '75112',
        '75013' => '75113', '75014' => '75114', '75015' => '75115', '75016' => '75116',
        '75017' => '75117', '75018' => '75118', '75019' => '75119', '75020' => '75120',
        
        // Seine-Saint-Denis (93)
        '93130' => '93055', // Noisy-le-Sec
        '93100' => '93048', // Montreuil
        '93200' => '93066', // Saint-Denis
        '93300' => '93001', // Aubervilliers
        '93400' => '93070', // Saint-Ouen-sur-Seine
        
        // Hauts-de-Seine (92)
        '92100' => '92012', // Boulogne-Billancourt
        '92200' => '92050', // Neuilly-sur-Seine
        '92300' => '92040', // Levallois-Perret
        
        // Val-de-Marne (94)
        '94300' => '94081', // Vincennes
        '94200' => '94037', // Ivry-sur-Seine
        
        // Autres grandes villes
        '69001' => '69381', '69002' => '69382', '69003' => '69383', // Lyon
        '13001' => '13201', '13002' => '13202', '13003' => '13203', // Marseille
        '33000' => '33063', // Bordeaux
        '31000' => '31555', // Toulouse
        '59000' => '59350', // Lille
        '67000' => '67482', // Strasbourg
        '44000' => '44109', // Nantes
        '34000' => '34172', // Montpellier
    ];
    
    // Mapping ville -> code INSEE pour les recherches par nom
    private array $cityToInsee = [
        // Paris utilise un code spécial pour toute la ville
        'paris' => '75000', // Code générique pour toute la ville de Paris
        'lyon' => '69123',
        'marseille' => '13055',
        'toulouse' => '31555',
        'nice' => '06088',
        'nantes' => '44109',
        'strasbourg' => '67482',
        'montpellier' => '34172',
        'bordeaux' => '33063',
        'lille' => '59350',
        'rennes' => '35238',
        'reims' => '51454',
        'le havre' => '76351',
        'saint-étienne' => '42218',
        'toulon' => '83137',
        'angers' => '49007',
        'grenoble' => '38185',
        'dijon' => '21231',
        'noisy-le-sec' => '93055',
        'montreuil' => '93048',
        'saint-denis' => '93066',
        'aubervilliers' => '93001',
    ];

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Convertit une localisation (code postal, nom de ville, etc.) en code INSEE
     * Retourne null si la conversion n'est pas possible
     */
    public function convertLocationToInsee(string $location): ?string
    {
        $location = trim($location);
        
        // Si c'est déjà un code INSEE (5 chiffres), le retourner
        if (preg_match('/^\d{5}$/', $location) && isset($this->postalToInsee[$location])) {
            $insee = $this->postalToInsee[$location];
            $this->logger->info('Conversion code postal vers INSEE', [
                'postal_code' => $location,
                'insee_code' => $insee
            ]);
            return $insee;
        }
        
        // Si c'est un nom de ville
        $cityKey = strtolower($location);
        if (isset($this->cityToInsee[$cityKey])) {
            $insee = $this->cityToInsee[$cityKey];
            $this->logger->info('Conversion nom de ville vers INSEE', [
                'city_name' => $location,
                'insee_code' => $insee
            ]);
            return $insee;
        }
        
        // Si c'est potentiellement déjà un code INSEE (5 chiffres), le laisser tel quel mais avec avertissement
        if (preg_match('/^\d{5}$/', $location)) {
            $this->logger->warning('Code INSEE non vérifié - utilisation directe', [
                'insee_code' => $location
            ]);
            return $location;
        }
        
        $this->logger->warning('Impossible de convertir la localisation - sera ignorée', [
            'location' => $location
        ]);
        
        return null;
    }
    
    /**
     * Valide qu'un code INSEE existe dans notre mapping
     */
    public function isValidLocation(string $location): bool
    {
        $location = trim($location);
        
        // Code postal connu
        if (isset($this->postalToInsee[$location])) {
            return true;
        }
        
        // Nom de ville connu
        $cityKey = strtolower($location);
        if (isset($this->cityToInsee[$cityKey])) {
            return true;
        }
        
        // Format code INSEE (on suppose que c'est valide)
        if (preg_match('/^\d{5}$/', $location)) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Retourne une liste de suggestions de localisation
     */
    public function getLocationSuggestions(string $query): array
    {
        $query = strtolower(trim($query));
        $suggestions = [];
        
        if (strlen($query) < 2) {
            return $suggestions;
        }
        
        // Recherche dans les noms de ville
        foreach ($this->cityToInsee as $city => $insee) {
            if (strpos($city, $query) !== false) {
                $suggestions[] = [
                    'label' => ucfirst($city),
                    'value' => $city,
                    'insee' => $insee
                ];
            }
        }
        
        // Recherche dans les codes postaux
        foreach ($this->postalToInsee as $postal => $insee) {
            if (strpos($postal, $query) === 0) {
                $suggestions[] = [
                    'label' => $postal,
                    'value' => $postal,
                    'insee' => $insee
                ];
            }
        }
        
        return array_slice($suggestions, 0, 10); // Limiter à 10 suggestions
    }
}
