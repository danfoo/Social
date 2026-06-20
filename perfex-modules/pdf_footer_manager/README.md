# PDF Footer Manager — module Perfex CRM

Configure **facilement le pied de page** des PDF de Perfex (factures, devis,
propositions, avoirs, contrats, paiements…) depuis une page d'admin, **sans
toucher au code core** et donc **sans rien perdre lors des mises à jour** de
Perfex.

Le footer est injecté automatiquement sur **chaque page** du PDF via le moteur
mPDF de Perfex (`SetHTMLFooter`).

## Installation

1. Copier le dossier `pdf_footer_manager/` dans le répertoire `modules/` de votre
   installation Perfex :

   ```
   modules/pdf_footer_manager/
   ```

   > ⚠️ C'est bien le **contenu** de `perfex-modules/pdf_footer_manager` de ce
   > dépôt qu'il faut placer dans `modules/pdf_footer_manager` côté Perfex.

2. Dans Perfex : **Setup → Modules**, repérer *PDF Footer Manager* et cliquer
   sur **Activate**. L'activation crée les options par défaut.

3. Aller dans **Setup → PDF Footer Manager** pour configurer le pied de page.

## Utilisation

- **Activer le pied de page PDF** : interrupteur général.
- **Pied de page global (HTML)** : appliqué à tous les documents. Vous pouvez
  coller du HTML simple (mPDF supporte un sous-ensemble de CSS *inline*).
- **Utiliser le pied de page global pour tous les documents** : si décoché, vous
  pouvez définir un pied de page **par type de document** (factures, devis…).
- **Marge basse** : espace réservé en bas de page pour le footer (en mm).

### Champs de fusion

Remplacés au rendu par les infos de la société Perfex :

`{company_name}` `{company_address}` `{company_city}` `{company_phone}`
`{company_email}` `{company_vat}` `{website}` `{year}`

Placeholders natifs **mPDF** (résolus page par page, à laisser tels quels) :

`{PAGENO}` (n° de page courant) · `{nbpg}` (nombre total de pages) ·
`{DATE j-m-Y}` (date du jour)

Exemple :

```html
<div style="border-top:1px solid #ccc;padding-top:6px;font-size:8px;color:#777;text-align:center;">
  {company_name} — {company_address} — TVA {company_vat}<br>
  Page {PAGENO} / {nbpg}
</div>
```

## Comment ça marche (technique)

- Le module s'enregistre sur les hooks de construction du PDF de Perfex
  (`pdf_construct` et alias), récupère l'instance `App_pdf` (qui étend
  `\Mpdf\Mpdf` sur Perfex ≥ 2.3) et appelle `SetHTMLFooter()`.
- Le **type de document** est déduit du nom de la classe PDF concrète
  (`Invoice_pdf`, `Estimate_pdf`, `Proposal_pdf`, `Credit_note_pdf`, …), ce qui
  permet des footers par type sans édition de vue.
- Aucun fichier de `application/views` ni `application/libraries` n'est modifié.

## Compatibilité

- **Perfex ≥ 2.3** (moteur mPDF) : pied de page injecté automatiquement.
- **Anciennes versions sous TCPDF** (pas de `SetHTMLFooter`) : le module se
  désactive proprement (aucune erreur). Pour ces versions, le pied de page doit
  être ajouté dans les vues `application/views/themes/perfex/views/*pdf.php`.

> Note : selon la version exacte de Perfex, le nom du hook de construction PDF
> peut varier. Le module écoute plusieurs noms candidats. Si le footer
> n'apparaît pas, vérifiez dans `application/libraries/App_pdf.php` le nom passé
> à `hooks()->do_action(...)` au moment de la construction et ajoutez-le dans
> `helpers/pdf_footer_manager_helper.php`.

## Désinstallation

Désactiver le module dans **Setup → Modules**. Les options restent en base ;
supprimez-les manuellement si besoin (préfixe `pdf_footer_manager_` dans
`tbloptions`).
