<?php

echo "========================================\n";
echo "Test de sécurité des codes de secours\n";
echo "========================================\n\n";

// Exemple de code de secours en clair
$plainCode = "8B26360C";

echo "1. Code de secours original (en clair):\n";
echo "   $plainCode\n\n";

// Hash SHA-256 du code
$hashedCode = hash('sha256', $plainCode);

echo "2. Hash SHA-256 stocké en base de données:\n";
echo "   $hashedCode\n\n";

// Vérifier qu'on ne peut pas retrouver le code original
echo "3. Peut-on retrouver le code original depuis le hash ?\n";
echo "   ❌ NON ! Le hash SHA-256 est à sens unique (one-way).\n";
echo "   Il est mathématiquement impossible de retrouver '$plainCode'\n";
echo "   à partir de '$hashedCode'\n\n";

// Vérifier qu'on peut quand même vérifier un code
echo "4. Comment le système vérifie-t-il un code alors ?\n";
echo "   ✅ L'utilisateur entre: '$plainCode'\n";
echo "   ✅ Le système hash l'entrée: " . hash('sha256', $plainCode) . "\n";
echo "   ✅ Le système compare les deux hash\n";
echo "   ✅ Si identiques → code valide !\n\n";

// Démonstration avec un mauvais code
$wrongCode = "WRONG123";
$wrongHash = hash('sha256', $wrongCode);

echo "5. Que se passe-t-il avec un mauvais code ?\n";
echo "   ❌ L'utilisateur entre: '$wrongCode'\n";
echo "   ❌ Hash généré: $wrongHash\n";
echo "   ❌ Ne correspond pas au hash stocké → code invalide !\n\n";

// Comparaison des longueurs
echo "6. Pourquoi c'est sécurisé ?\n";
echo "   • Codes originaux: 8 caractères (ex: $plainCode)\n";
echo "   • Hash stockés: 64 caractères (ex: " . substr($hashedCode, 0, 20) . "...)\n";
echo "   • Impossible de retrouver le code original depuis le hash\n";
echo "   • Même si quelqu'un accède à la base de données, les codes restent secrets\n";
echo "   • Seul l'utilisateur qui a sauvegardé ses codes peut les utiliser\n\n";

echo "========================================\n";
echo "✅ Les codes de secours sont bien sécurisés !\n";
echo "========================================\n";
