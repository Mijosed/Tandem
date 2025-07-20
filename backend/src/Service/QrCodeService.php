<?php

namespace App\Service;

class QrCodeService
{
    /**
     * Génère un QR Code SVG pour une URL donnée
     * Implémentation simple sans librairie externe
     */
    public function generateSvg(string $data, int $size = 200): string
    {
        // Pour une implémentation complète, on devrait implémenter l'algorithme QR Code
        // Ici, on utilise une approche simplifiée avec un service externe ou on retourne
        // directement l'URL pour que le frontend puisse générer le QR code
        
        // Alternative : utiliser l'API Google Charts (simple mais nécessite une connexion)
        $encodedData = urlencode($data);
        $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size={$size}x{$size}&data={$encodedData}";
        
        // Retourner un SVG qui embed l'image ou l'URL directement
        return $this->createSvgWithUrl($qrUrl, $size);
    }

    /**
     * Génère un QR Code en base64 pour intégration directe
     */
    public function generateBase64(string $data, int $size = 200): string
    {
        // Utiliser l'API QR Server pour générer le QR code
        $encodedData = urlencode($data);
        $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size={$size}x{$size}&data={$encodedData}&format=png";
        
        try {
            $imageData = file_get_contents($qrUrl);
            if ($imageData === false) {
                throw new \Exception('Impossible de générer le QR code');
            }
            
            return 'data:image/png;base64,' . base64_encode($imageData);
        } catch (\Exception $e) {
            // Fallback : retourner une image d'erreur simple
            return $this->generateErrorQrCode($size);
        }
    }

    /**
     * Retourne l'URL directe du QR code (pour déléguer au frontend)
     */
    public function getQrCodeUrl(string $data, int $size = 200): string
    {
        $encodedData = urlencode($data);
        return "https://api.qrserver.com/v1/create-qr-code/?size={$size}x{$size}&data={$encodedData}";
    }

    /**
     * Crée un SVG simple avec une image embarquée
     */
    private function createSvgWithUrl(string $imageUrl, int $size): string
    {
        return "
        <svg width=\"{$size}\" height=\"{$size}\" xmlns=\"http://www.w3.org/2000/svg\">
            <image href=\"{$imageUrl}\" width=\"{$size}\" height=\"{$size}\" />
        </svg>";
    }

    /**
     * Génère une image d'erreur simple en base64
     */
    private function generateErrorQrCode(int $size): string
    {
        // Créer une image simple avec GD si disponible
        if (extension_loaded('gd')) {
            $image = imagecreate($size, $size);
            $white = imagecolorallocate($image, 255, 255, 255);
            $black = imagecolorallocate($image, 0, 0, 0);
            
            imagefill($image, 0, 0, $white);
            
            // Dessiner un motif simple
            for ($i = 0; $i < $size; $i += 10) {
                for ($j = 0; $j < $size; $j += 10) {
                    if (($i + $j) % 20 === 0) {
                        imagefilledrectangle($image, $i, $j, $i + 5, $j + 5, $black);
                    }
                }
            }
            
            ob_start();
            imagepng($image);
            $imageData = ob_get_contents();
            ob_end_clean();
            imagedestroy($image);
            
            return 'data:image/png;base64,' . base64_encode($imageData);
        }
        
        // Fallback : retourner une chaîne d'erreur
        return 'data:text/plain;base64,' . base64_encode('QR Code generation failed');
    }

    /**
     * Implémentation basique d'un générateur QR Code (très simplifiée)
     * Pour une utilisation en production, il faudrait une implémentation complète
     */
    public function generateSimpleQrCode(string $data, int $size = 200): string
    {
        // Implémentation très basique - ne convient que pour des tests
        // En production, utiliser une vraie librairie QR Code ou l'API externe
        
        $matrix = $this->createSimpleMatrix($data);
        return $this->matrixToSvg($matrix, $size);
    }

    /**
     * Crée une matrice simple (ne suit pas le standard QR Code complet)
     */
    private function createSimpleMatrix(string $data): array
    {
        $size = 21; // Taille minimale QR Code
        $matrix = array_fill(0, $size, array_fill(0, $size, 0));
        
        // Ajouter les motifs de positionnement (coins)
        $this->addFinderPattern($matrix, 0, 0);
        $this->addFinderPattern($matrix, $size - 7, 0);
        $this->addFinderPattern($matrix, 0, $size - 7);
        
        // Encoder les données de manière très simplifiée
        $hash = md5($data);
        for ($i = 0; $i < strlen($hash); $i++) {
            $row = ($i * 2) % $size;
            $col = ($i * 3) % $size;
            $matrix[$row][$col] = hexdec($hash[$i]) % 2;
        }
        
        return $matrix;
    }

    /**
     * Ajoute un motif de positionnement au QR Code
     */
    private function addFinderPattern(array &$matrix, int $x, int $y): void
    {
        for ($i = 0; $i < 7; $i++) {
            for ($j = 0; $j < 7; $j++) {
                if ($x + $i < count($matrix) && $y + $j < count($matrix[0])) {
                    $matrix[$x + $i][$y + $j] = 
                        ($i === 0 || $i === 6 || $j === 0 || $j === 6 ||
                         ($i >= 2 && $i <= 4 && $j >= 2 && $j <= 4)) ? 1 : 0;
                }
            }
        }
    }

    /**
     * Convertit une matrice en SVG
     */
    private function matrixToSvg(array $matrix, int $size): string
    {
        $matrixSize = count($matrix);
        $cellSize = $size / $matrixSize;
        
        $svg = "<svg width=\"{$size}\" height=\"{$size}\" xmlns=\"http://www.w3.org/2000/svg\">";
        $svg .= "<rect width=\"{$size}\" height=\"{$size}\" fill=\"white\"/>";
        
        for ($i = 0; $i < $matrixSize; $i++) {
            for ($j = 0; $j < $matrixSize; $j++) {
                if ($matrix[$i][$j] === 1) {
                    $x = $j * $cellSize;
                    $y = $i * $cellSize;
                    $svg .= "<rect x=\"{$x}\" y=\"{$y}\" width=\"{$cellSize}\" height=\"{$cellSize}\" fill=\"black\"/>";
                }
            }
        }
        
        $svg .= "</svg>";
        return $svg;
    }
} 