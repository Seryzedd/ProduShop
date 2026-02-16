<?php

namespace App\Service;

/**
 * Service de génération de mots de passe sécurisés
 * GARANTI conforme au pattern : min 8 caractères + majuscule + minuscule + chiffre
 */
class PasswordGenerator
{
    private const LOWERCASE = 'abcdefghijklmnopqrstuvwxyz';
    private const UPPERCASE = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    private const DIGITS = '0123456789';
    private const SPECIAL_CHARS = '!@#$%^&*()_+-=[]{}|;:,.<>?';
    private const AMBIGUOUS_CHARS = 'il1Lo0O';
    
    // Pattern requis : min 8 caractères + majuscule + minuscule + chiffre
    private const MIN_LENGTH = 8;
    private const REQUIRED_PATTERN = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/';
    
    /**
     * Génère un mot de passe aléatoire sécurisé
     * GARANTIT la conformité avec le pattern requis
     *
     * @param int $length Longueur du mot de passe (min: 8)
     * @param bool $includeLowercase Inclure des minuscules (REQUIS pour pattern)
     * @param bool $includeUppercase Inclure des majuscules (REQUIS pour pattern)
     * @param bool $includeDigits Inclure des chiffres (REQUIS pour pattern)
     * @param bool $includeSpecial Inclure des caractères spéciaux
     * @param bool $excludeAmbiguous Exclure les caractères ambigus (i, l, 1, L, o, 0, O)
     * @return string
     * @throws \InvalidArgumentException
     */
    public function generate(
        int $length = 12,
        bool $includeLowercase = true,
        bool $includeUppercase = true,
        bool $includeDigits = true,
        bool $includeSpecial = true,
        bool $excludeAmbiguous = false
    ): string {
        if ($length < self::MIN_LENGTH) {
            throw new \InvalidArgumentException(
                sprintf('La longueur minimale du mot de passe est %d caractères', self::MIN_LENGTH)
            );
        }
        
        // Forcer les paramètres requis pour respecter le pattern
        if (!$includeLowercase || !$includeUppercase || !$includeDigits) {
            throw new \InvalidArgumentException(
                'Pour respecter le pattern requis, le mot de passe DOIT contenir : ' .
                'minuscules + majuscules + chiffres'
            );
        }
        
        // Construire le jeu de caractères
        $charset = '';
        $required = [];
        
        $lowercase = self::LOWERCASE;
        $uppercase = self::UPPERCASE;
        $digits = self::DIGITS;
        
        if ($excludeAmbiguous) {
            $ambiguous = str_split(self::AMBIGUOUS_CHARS);
            $lowercase = str_replace($ambiguous, '', $lowercase);
            $uppercase = str_replace($ambiguous, '', $uppercase);
            $digits = str_replace($ambiguous, '', $digits);
        }
        
        $charset .= $lowercase;
        $required['lowercase'] = $lowercase;
        
        $charset .= $uppercase;
        $required['uppercase'] = $uppercase;
        
        $charset .= $digits;
        $required['digits'] = $digits;
        
        if ($includeSpecial) {
            $charset .= self::SPECIAL_CHARS;
            $required['special'] = self::SPECIAL_CHARS;
        }
        
        // Générer le mot de passe avec garantie de conformité
        $password = $this->generateCompliantPassword($length, $charset, $required);
        
        // Vérification finale (sécurité)
        if (!$this->validatePattern($password)) {
            throw new \RuntimeException('Erreur: le mot de passe généré ne respecte pas le pattern requis');
        }
        
        return $password;
    }
    
    /**
     * Génère un mot de passe en garantissant la présence des caractères requis
     */
    private function generateCompliantPassword(int $length, string $charset, array $required): string
    {
        $password = '';
        
        // ÉTAPE 1 : S'assurer qu'au moins un caractère de chaque type REQUIS est présent
        // Ceci GARANTIT la conformité avec le pattern
        foreach ($required as $type => $chars) {
            $password .= $chars[random_int(0, strlen($chars) - 1)];
        }
        
        // ÉTAPE 2 : Compléter avec des caractères aléatoires
        $remainingLength = $length - strlen($password);
        $charsetLength = strlen($charset);
        
        for ($i = 0; $i < $remainingLength; $i++) {
            $password .= $charset[random_int(0, $charsetLength - 1)];
        }
        
        // ÉTAPE 3 : Mélanger les caractères pour éviter un pattern prévisible
        // (les caractères requis ne sont plus au début)
        return str_shuffle($password);
    }
    
    /**
     * Valide qu'un mot de passe respecte le pattern requis
     *
     * @param string $password
     * @return bool
     */
    public function validatePattern(string $password): bool
    {
        // Vérifier la longueur minimale
        if (strlen($password) < self::MIN_LENGTH) {
            return false;
        }
        
        // Vérifier le pattern : majuscule + minuscule + chiffre
        if (!preg_match(self::REQUIRED_PATTERN, $password)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Génère un mot de passe fort (12 caractères avec tous les types)
     * ✅ GARANTI conforme au pattern
     *
     * @return string
     */
    public function generateStrong(): string
    {
        return $this->generate(
            length: 12,
            includeLowercase: true,
            includeUppercase: true,
            includeDigits: true,
            includeSpecial: true,
            excludeAmbiguous: false
        );
    }
    
    /**
     * Génère un mot de passe facile à lire (sans caractères ambigus)
     * ✅ GARANTI conforme au pattern
     *
     * @return string
     */
    public function generateReadable(): string
    {
        return $this->generate(
            length: 12,
            includeLowercase: true,
            includeUppercase: true,
            includeDigits: true,
            includeSpecial: false,  // Pas de caractères spéciaux pour faciliter la lecture
            excludeAmbiguous: true
        );
    }
    
    /**
     * Génère une passphrase (mots séparés par des tirets)
     * ✅ GARANTI conforme au pattern (contient majuscule au début + chiffres à la fin)
     *
     * @param int $wordCount Nombre de mots
     * @return string
     */
    public function generatePassphrase(int $wordCount = 4): string
    {
        $words = [
            'soleil', 'lune', 'etoile', 'mer', 'montagne', 'foret', 'riviere', 'ocean',
            'chat', 'chien', 'oiseau', 'arbre', 'fleur', 'nuage', 'vent', 'pluie',
            'rouge', 'bleu', 'vert', 'jaune', 'noir', 'blanc', 'orange', 'violet',
            'grand', 'petit', 'beau', 'rapide', 'lent', 'fort', 'doux', 'brillant',
            'maison', 'porte', 'fenetre', 'jardin', 'route', 'pont', 'ville', 'pays',
            'temps', 'jour', 'nuit', 'heure', 'minute', 'seconde', 'annee', 'mois',
            'livre', 'musique', 'danse', 'chant', 'art', 'couleur', 'forme', 'ligne',
            'coeur', 'esprit', 'ame', 'reve', 'joie', 'paix', 'amour', 'vie'
        ];
        
        $selectedWords = [];
        $wordListLength = count($words);
        
        for ($i = 0; $i < $wordCount; $i++) {
            $selectedWords[] = $words[random_int(0, $wordListLength - 1)];
        }
        
        // Ajouter un chiffre à la fin pour garantir la conformité
        $number = random_int(10, 99);
        
        // Première lettre en majuscule pour garantir la présence d'une majuscule
        $passphrase = ucfirst(implode('-', $selectedWords)) . '-' . $number;
        
        // Vérification : la passphrase contient forcément :
        // - une majuscule (ucfirst)
        // - des minuscules (les mots)
        // - des chiffres (le nombre à la fin)
        
        if (!$this->validatePattern($passphrase)) {
            throw new \RuntimeException('Erreur: la passphrase ne respecte pas le pattern requis');
        }
        
        return $passphrase;
    }
    
    /**
     * Génère un mot de passe prononçable (alternance consonne-voyelle)
     * ✅ GARANTI conforme au pattern
     *
     * @param int $length Longueur du mot de passe
     * @return string
     */
    public function generatePronounceable(int $length = 12): string
    {
        if ($length < self::MIN_LENGTH) {
            $length = self::MIN_LENGTH;
        }
        
        $consonants = 'bcdfghjklmnprstvwxz';
        $vowels = 'aeiou';
        
        $password = '';
        $useConsonant = random_int(0, 1) === 1;
        
        // Générer la base prononçable
        $baseLength = $length - 3; // Réserver 3 caractères pour garantir le pattern
        
        for ($i = 0; $i < $baseLength; $i++) {
            if ($useConsonant) {
                $password .= $consonants[random_int(0, strlen($consonants) - 1)];
            } else {
                $password .= $vowels[random_int(0, strlen($vowels) - 1)];
            }
            $useConsonant = !$useConsonant;
        }
        
        // Garantir la conformité au pattern en ajoutant :
        // - Une majuscule (transformer un caractère aléatoire)
        $randomPos = random_int(0, strlen($password) - 1);
        $password[$randomPos] = strtoupper($password[$randomPos]);
        
        // - Un chiffre
        $password .= random_int(0, 9);
        
        // - Un autre caractère pour atteindre la longueur
        $password .= $consonants[random_int(0, strlen($consonants) - 1)];
        
        // Ajouter un chiffre supplémentaire si nécessaire
        if (!preg_match('/\d/', $password)) {
            $password .= random_int(0, 9);
        }
        
        // S'assurer qu'il y a au moins une minuscule
        if (!preg_match('/[a-z]/', $password)) {
            $password .= 'a';
        }
        
        // Vérification finale
        if (!$this->validatePattern($password)) {
            // Fallback : générer un mot de passe standard conforme
            return $this->generateStrong();
        }
        
        return substr($password, 0, $length);
    }
    
    /**
     * ❌ DÉSACTIVÉ : generatePin() ne peut pas respecter le pattern
     * (un PIN ne contient que des chiffres)
     * 
     * Utilisez generatePin() uniquement pour des codes de vérification,
     * PAS pour des mots de passe utilisateur !
     */
    public function generatePin(int $length = 4): string
    {
        throw new \BadMethodCallException(
            'generatePin() ne peut pas respecter le pattern requis (majuscule + minuscule + chiffre). ' .
            'Utilisez generateStrong() ou generateReadable() pour un mot de passe valide.'
        );
    }
    
    /**
     * Génère plusieurs mots de passe à la fois
     * ✅ TOUS garantis conformes au pattern
     *
     * @param int $count Nombre de mots de passe à générer
     * @param int $length Longueur de chaque mot de passe
     * @return array
     */
    public function generateMultiple(int $count = 5, int $length = 12): array
    {
        $passwords = [];
        for ($i = 0; $i < $count; $i++) {
            $passwords[] = $this->generate($length);
        }
        return $passwords;
    }
    
    /**
     * Évalue la force d'un mot de passe
     *
     * @param string $password
     * @return array ['score' => int (0-100), 'strength' => string, 'suggestions' => array, 'valid' => bool]
     */
    public function evaluateStrength(string $password): array
    {
        $score = 0;
        $suggestions = [];
        
        // Vérifier la conformité au pattern requis
        $valid = $this->validatePattern($password);
        
        if (!$valid) {
            $suggestions[] = '❌ Ne respecte pas le pattern requis (min 8 caractères + majuscule + minuscule + chiffre)';
        }
        
        // Longueur
        $length = strlen($password);
        if ($length >= 8) $score += 20;
        if ($length >= 12) $score += 10;
        if ($length >= 16) $score += 10;
        if ($length < 8) {
            $suggestions[] = 'Utilisez au moins 8 caractères';
        }
        
        // Minuscules
        if (preg_match('/[a-z]/', $password)) {
            $score += 15;
        } else {
            $suggestions[] = 'Ajoutez des lettres minuscules';
        }
        
        // Majuscules
        if (preg_match('/[A-Z]/', $password)) {
            $score += 15;
        } else {
            $suggestions[] = 'Ajoutez des lettres majuscules';
        }
        
        // Chiffres
        if (preg_match('/[0-9]/', $password)) {
            $score += 15;
        } else {
            $suggestions[] = 'Ajoutez des chiffres';
        }
        
        // Caractères spéciaux
        if (preg_match('/[^a-zA-Z0-9]/', $password)) {
            $score += 15;
        } else {
            $suggestions[] = 'Ajoutez des caractères spéciaux pour plus de sécurité';
        }
        
        // Variété de caractères
        $uniqueChars = count(array_unique(str_split($password)));
        if ($uniqueChars >= $length * 0.5) {
            $score += 10;
        }
        
        // Déterminer la force
        $strength = match(true) {
            $score >= 80 => 'Très fort',
            $score >= 60 => 'Fort',
            $score >= 40 => 'Moyen',
            $score >= 20 => 'Faible',
            default => 'Très faible'
        };
        
        return [
            'score' => $score,
            'strength' => $strength,
            'suggestions' => $suggestions,
            'valid' => $valid,  // ✅ Indique si le pattern est respecté
        ];
    }
    
    /**
     * Obtient le pattern requis pour information
     *
     * @return array
     */
    public function getRequiredPattern(): array
    {
        return [
            'minLength' => self::MIN_LENGTH,
            'pattern' => self::REQUIRED_PATTERN,
            'description' => 'Minimum 8 caractères avec au moins une majuscule, une minuscule et un chiffre',
            'requirements' => [
                'Longueur minimale : 8 caractères',
                'Au moins une lettre minuscule (a-z)',
                'Au moins une lettre majuscule (A-Z)',
                'Au moins un chiffre (0-9)',
            ],
        ];
    }
}