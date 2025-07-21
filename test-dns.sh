#!/bin/bash
echo "=== Test de connectivité DNS ==="
echo "Domaine principal:"
nslookup tandems.social
echo ""
echo "API:"
nslookup api.tandems.social
echo ""
echo "Matomo:"
nslookup matomo.tandems.social
echo ""
echo "Mail:"
nslookup mail.tandems.social
echo ""
echo "=== Test HTTP (depuis l'extérieur) ==="
echo "Test tandems.social:"
curl -I http://tandems.social || echo "ECHEC"
echo ""
echo "Test api.tandems.social:"
curl -I http://api.tandems.social || echo "ECHEC"
