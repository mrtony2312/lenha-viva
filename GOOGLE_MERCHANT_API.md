# Google Merchant API — Naturalenha

Intégration Laravel avec la **Google Merchant API** actuelle (`products/v1` + `datasources/v1`).

Ce n’est **pas** le legacy Content API for Shopping.  
Le flux XML `/feed/google-merchant.xml` reste actif jusqu’à validation complète de l’API.

Documentation Google : https://developers.google.com/merchant/api/

---

## Marché

| Paramètre | Valeur |
|-----------|--------|
| Pays | PT (Portugal Continental uniquement) |
| Langue | pt |
| Devise | EUR |
| Feed label | PT |
| Exclusions ads | ES, FR, DE, IT, GB, US, AD, BE |

Ne pas activer Espagne / France / Allemagne / Italie / Belgique dans Merchant Center.

---

## Prérequis Google

1. Compte [Merchant Center](https://merchants.google.com/) pour `naturalenha.com`
2. Projet Google Cloud avec **Merchant API** activée
3. [Developer registration](https://developers.google.com/merchant/api/guides/quickstart) liée au Merchant Center
4. Auth au choix :
   - **OAuth 2.0** (recommandé ici) : Client ID + Secret + Refresh token (scope `https://www.googleapis.com/auth/content`)
   - **Service account** : JSON + e-mail ajouté comme utilisateur Merchant Center
5. Data source **Primary** de type **API** (une seule, réutilisée)

---

## Variables `.env`

```env
APP_URL=https://naturalenha.com

GOOGLE_MERCHANT_ACCOUNT_ID=
GOOGLE_MERCHANT_DATA_SOURCE_ID=
GOOGLE_MERCHANT_DATA_SOURCE_NAME=

GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REFRESH_TOKEN=

# Alternative si pas d’OAuth :
GOOGLE_MERCHANT_CREDENTIALS=storage/app/google/merchant-service-account.json

GOOGLE_MERCHANT_LANGUAGE=pt
GOOGLE_MERCHANT_FEED_LABEL=PT
GOOGLE_MERCHANT_COUNTRY=PT
GOOGLE_MERCHANT_EXCLUDED_COUNTRIES=ES,FR,DE,IT,GB,US,AD,BE
GOOGLE_MERCHANT_QUEUE=default
```

**Sécurité**

- `.env` est gitignored
- `storage/app/google/*.json` est gitignored
- Les tokens ne sont jamais loggés (redaction dans `GoogleMerchantApiException`)

`APP_URL` **doit** être `https://naturalenha.com` pour toute synchro réelle (Google refuse localhost).

---

## Installation

```bash
composer install
cp .env.example .env   # si besoin
# renseigner les variables Merchant + APP_URL HTTPS
php artisan migrate
php artisan merchant:status
php artisan merchant:datasource --ensure
# coller GOOGLE_MERCHANT_DATA_SOURCE_ID dans .env puis :
php artisan config:clear
```

---

## Commandes Artisan

| Commande | Rôle |
|----------|------|
| `php artisan merchant:status` | Santé API + compteurs tracking |
| `php artisan merchant:datasource --list` | Lister les data sources |
| `php artisan merchant:datasource --ensure` | Réutiliser ou créer **une** primary API source |
| `php artisan merchant:sync-product 5625` | Tester **un** produit (insert) |
| `php artisan merchant:sync-product 5625 --update` | Patch |
| `php artisan merchant:sync-product 5625 --status` | `products.get` |
| `php artisan merchant:sync-product 5625 --delete` | Supprimer l’input |
| `php artisan merchant:sync --dry-run` | Lister sans appeler Google |
| `php artisan merchant:sync --limit=10` | Envoyer 10 produits |
| `php artisan merchant:sync` | Catalogue éligible complet |

---

## Procédure de premier test (obligatoire)

1. `php artisan merchant:status` → `api_connection = true`
2. `php artisan merchant:datasource --ensure`
3. Choisir un produit réel (ex. **5625** Ardenforest Pellets)
4. `php artisan merchant:sync-product 5625`
5. Vérifier dans Merchant Center → Products
6. `php artisan merchant:sync-product 5625 --status`
7. Modifier prix/stock dans `config/loja_products.php` puis `--update`
8. Puis seulement : `--limit=10` → `--limit=50` → sync complet

---

## Architecture Laravel

```
CatalogProduct (config/loja_products.php)
    → MerchantCatalog (mapping attributs)
    → GoogleMerchantService
        → GoogleMerchantClient (OAuth ou service account)
        → productInputs.insert / patch / delete
        → products.get
    → google_merchant_products (tracking)
```

Events / queue :

```
CatalogProductChanged
  → QueueGoogleMerchantSync
    → SyncProductToGoogleMerchant (unique, retry, backoff)
```

Comme le catalogue est un fichier PHP (pas Eloquent), les events ne partent pas tout seuls à l’édition du fichier. Utiliser :

```php
event(new \App\Events\CatalogProductChanged(5625, 'sync'));
```

ou le scheduler quotidien `merchant:sync` (06:15).

---

## Mapping produits

- `offerId` : `lv-{id}` (stable)
- Prix : `price` / `salePrice` (EUR micros) alignés sur la fiche
- Stock : `IN_STOCK` / `OUT_OF_STOCK` depuis `in_stock`
- Brand : fabricant détecté (Ardenforest, Solid, …) sinon Naturalenha
- GTIN : **jamais inventé** (absent du catalogue)
- MPN : `ref` si valide
- Sans GTIN + marque magasin : `identifierExists = false`
- Catégories Google : `config/merchant_categories.php`
- Images : URLs HTTPS publiques (`asset()` + `APP_URL`)
- Unicode PT (ã, ç, €…) conservé en UTF-8

---

## Scheduler / Queue

- Scheduler : `merchant:sync` daily 06:15
- Queue : `QUEUE_CONNECTION=database` (déjà dans le projet)
- Worker : `php artisan queue:work`

Cron Hostinger :

```text
* * * * * php /path/to/artisan schedule:run >> /dev/null 2>&1
```

---

## XML legacy

`GET /feed/google-merchant.xml` reste en place.  
Ne le désactiver qu’après validation API (insert / update / delete / statuts OK).

---

## Dépannage

| Symptôme | Cause probable |
|----------|----------------|
| `Merchant API not ready` | Env Account / Data Source / OAuth manquants |
| `APP_URL must be the public HTTPS shop` | `.env` encore en localhost |
| 401 / 403 | Refresh token ou droits Merchant Center |
| 404 après insert | Attendre quelques minutes puis `products.get` |
| Misrepresentation ES | Marché ES encore actif dans Merchant Center |

---

## Tests

```bash
php artisan test --filter=Merchant
```
