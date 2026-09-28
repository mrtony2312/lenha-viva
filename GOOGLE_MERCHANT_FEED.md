# Google Merchant — flux XML (recommandé)

URL publique du feed :

```text
https://naturalenha.com/feed/google-merchant.xml
```

Ce flux RSS 2.0 + namespace `g:` est le moyen **le plus stable** pour garder les produits en ligne.
Il ne dépend **pas** d’un refresh token OAuth (qui peut expirer et bloquer l’API).

Références Google :
- [RSS 2.0](https://support.google.com/merchants/answer/160589)
- [Product data specification](https://support.google.com/merchants/answer/7052112)

---

## Pourquoi tu ne te réveilles pas avec 0 produits

Google retire un produit **seulement** s’il disparaît du feed (ou après des échecs répétés de récupération).

Protections dans le code Laravel :

1. **IDs stables** `lv-{id}` — jamais aléatoires  
2. **Catalogue complet** à chaque génération (pas un diff)  
3. **Refus d’écraser** un bon cache par un feed à **0 items**  
4. En cas d’erreur de build → **sert l’ancien feed** (stale) au lieu d’un 500 vide  
5. Liens / images toujours en **HTTPS** `naturalenha.com`  
6. Marché **PT only** + `shopping_ads_excluded_country` pour ES, etc.

---

## Configuration Merchant Center (à faire une fois)

1. Ouvre [Merchant Center](https://merchants.google.com) → **Produits** → **Add-ons / Sources de données**  
2. Crée une source **Produits** → type **Scheduled fetch** (récupération planifiée)  
3. URL :
   ```text
   https://naturalenha.com/feed/google-merchant.xml
   ```
4. Format : **XML**  
5. Pays / langue / feed label : **Portugal / pt / PT**  
6. Fréquence : **quotidienne** (ex. tous les matins)  
7. Enregistre et lance un fetch immédiat  

### Important

- Une seule source **PRIMARY** pour les produits.  
- Si tu avais une source **API**, ne la laisse pas écraser / vider le flux :  
  - soit tu passes le XML en primary,  
  - soit tu désactives la source API.  
- Ne mets **pas** Espagne comme pays de vente.

---

## Vérifications Laravel

```bash
php artisan merchant:feed-check
php artisan merchant:feed-check --live
```

Attendu : ~86 `<item>`, URLs HTTPS, namespace `xmlns:g` OK.

---

## Attributs envoyés par item

| Attribut | Valeur |
|----------|--------|
| `g:id` | `lv-{id}` stable |
| `g:title` / `g:description` | UTF-8 PT (accents OK) |
| `g:link` / `g:image_link` | HTTPS public |
| `g:availability` | `in_stock` / `out_of_stock` |
| `g:condition` | `new` |
| `g:price` / `g:sale_price` | EUR |
| `g:brand` | fabricant ou Naturalenha |
| `g:mpn` | `ref` si présent |
| `g:identifier_exists` | `no` si pas d’identifiant |
| `g:google_product_category` | taxonomie Google |
| `g:shipping` | PT, 0 EUR, délais handling/transit |
| `g:shopping_ads_excluded_country` | ES, FR, DE, … |
| `g:return_policy_label` | `portugal-14-dias` |

---

## Checklist anti “0 produit”

- [ ] Feed live répond **200** avec des `<item>`  
- [ ] Fetch planifié **quotidien** actif dans Merchant Center  
- [ ] `APP_URL=https://naturalenha.com` en production  
- [ ] Pas de déploiement qui vide `config/loja_products.php`  
- [ ] Ne pas remplacer le feed par un fichier vide  
- [ ] Après mise en ligne du nouveau code : `php artisan cache:clear` puis `merchant:feed-check --live`
