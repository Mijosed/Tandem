#!/usr/bin/env node

/**
 * Script de génération automatique du sitemap pour Tandem
 * Usage: node scripts/generate-sitemap.js
 */

const fs = require('fs');
const path = require('path');

const HOSTNAME = 'https://tandems.social';
const SITEMAP_PATH = path.join(__dirname, '..', 'public', 'sitemap.xml');

// Configuration des URLs avec leurs propriétés SEO
const routes = [
  { url: '/', priority: 1.0, changefreq: 'weekly' },
  { url: '/about', priority: 0.8, changefreq: 'monthly' },
  { url: '/contact', priority: 0.7, changefreq: 'monthly' },
  { url: '/pricing', priority: 0.9, changefreq: 'monthly' },
  { url: '/premium', priority: 0.9, changefreq: 'monthly' },
  { url: '/login', priority: 0.6, changefreq: 'yearly' },
  { url: '/register', priority: 0.6, changefreq: 'yearly' },
  { url: '/privacy', priority: 0.3, changefreq: 'yearly' },
  { url: '/terms', priority: 0.3, changefreq: 'yearly' },
  { url: '/legal', priority: 0.3, changefreq: 'yearly' },
  { url: '/blog', priority: 0.7, changefreq: 'weekly' },
  { url: '/help', priority: 0.5, changefreq: 'monthly' },
  { url: '/faq', priority: 0.5, changefreq: 'monthly' },
  { url: '/fonctionnalites', priority: 0.7, changefreq: 'monthly' },
  { url: '/comment-ca-marche', priority: 0.7, changefreq: 'monthly' },
  { url: '/secteurs/informatique', priority: 0.6, changefreq: 'monthly' },
  { url: '/secteurs/commerce', priority: 0.6, changefreq: 'monthly' },
  { url: '/secteurs/sante', priority: 0.6, changefreq: 'monthly' },
  { url: '/secteurs/education', priority: 0.6, changefreq: 'monthly' },
];

function generateSitemap() {
  const currentDate = new Date().toISOString().split('T')[0];
  
  let xml = `<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9
        http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">

`;

  routes.forEach(route => {
    xml += `  <url>
    <loc>${HOSTNAME}${route.url}</loc>
    <lastmod>${currentDate}</lastmod>
    <changefreq>${route.changefreq}</changefreq>
    <priority>${route.priority}</priority>
  </url>

`;
  });

  xml += '</urlset>';

  fs.writeFileSync(SITEMAP_PATH, xml);
  console.log(`✅ Sitemap généré avec ${routes.length} URLs dans ${SITEMAP_PATH}`);
}

// Execution du script
generateSitemap();
