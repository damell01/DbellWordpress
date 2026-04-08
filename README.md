# DBell Creations — Production Website

**Website:** [dbellcreations.com](https://www.dbellcreations.com)  
**Business:** DBell Creations — Full-Service Digital Agency  
**Location:** Fairhope, AL (Baldwin County)  
**Phone:** 251-406-2292  
**Email:** dbellcreations@gmail.com

---

## Overview

This is the production codebase for the DBell Creations website. The site is a static HTML/CSS/JS website built on a customized Bootstrap 5 template. It covers web design, custom software development, business automation, SEO services, and digital marketing.

---

## Site Structure

```
/ (root)
├── index.html                  — Homepage
├── about.html                  — About page
├── contact.html                — Contact page
├── pricing.html                — Pricing packages
├── project.html                — Portfolio / case studies
├── frequentlyaskedquestions.html — FAQ (with FAQPage schema)
├── tools.html                  — Free SEO & web design tools
├── webDesign.html              — Web Design service page
├── software.html               — Custom Software service page
├── automations.html            — Business Automation service page
├── marketing.html              — Digital Marketing service page
├── seo.html                    — SEO Optimization service page
├── scan.html                   — Free Website Scanner (redirects to WebsiteScan/)
│
├── — Local SEO Landing Pages —
├── web-design-fairhope-al.html
├── seo-fairhope-al.html
├── custom-software-development-alabama.html
├── automation-services-alabama.html
├── website-redesign-alabama.html
│
├── blog/
│   ├── index.html              — Blog listing page (search + filter)
│   └── [article].html          — 34 individual blog posts (see Blog section)
│
├── WebsiteScan/                — PHP-based website audit tool (separate app)
│   └── ...
│
├── css/
│   ├── bootstrap.min.css
│   └── style.css               — All custom styles (CSS variables, components)
│
├── js/
│   └── main.js                 — All custom JavaScript
│
├── img/                        — Images and icons
├── lib/                        — JS/CSS libraries (WOW.js, Owl Carousel, etc.)
│
├── sitemap.xml                 — XML sitemap (submit to Google Search Console)
├── robots.txt                  — Robots crawl rules
└── 404.html                    — Custom 404 error page
```

---

## Technology Stack

| Technology | Purpose |
|---|---|
| HTML5 / CSS3 | Core markup and styling |
| Bootstrap 5 | Responsive grid and components |
| jQuery 3.4 | DOM manipulation and plugins |
| WOW.js + Animate.css | Scroll animations |
| Owl Carousel | Testimonial/carousel sliders |
| Isotope | Portfolio filtering |
| Lightbox | Image/portfolio overlays |
| Font Awesome 5 | Icons |
| Bootstrap Icons | Secondary icons |
| Google Fonts (Heebo + Jost) | Typography |

---

## CSS Variables (Brand Colors)

Defined in `css/style.css`:

```css
--primary: #6222CC    /* Purple */
--secondary: #FBA504  /* Amber/Orange */
--light: #F6F4F9      /* Light purple-gray background */
--dark: #04000B       /* Near-black */
```

---

## Blog Posts

The blog (`blog/index.html`) contains 34+ articles across these categories:

**Web Design:** affordable web design, contractor websites, website redesign checklist, mobile-first design, web design trends, signs you need a redesign, website cost in Alabama, why your site isn't getting leads, website accessibility guide

**SEO:** local SEO guide Alabama, local SEO for service businesses, Google My Business guide, how to get more Google reviews, SEO vs PPC, website speed optimization, how to rank on Google Maps Alabama

**Digital Marketing:** social media marketing, content marketing, email marketing, digital marketing budget, lead generation strategies

**Automation & AI:** business automation intro, workflow automation guide, automate invoicing, AI tools for small business, ChatGPT prompts for small business

**E-commerce & Software:** e-commerce guide Alabama, custom software vs off-the-shelf, best CRM for Alabama businesses

---

## Free Tools Page (`tools.html`)

The site includes a free interactive tools page with:

1. **Free Website Scanner** — launches the WebsiteScan audit tool
2. **Google SERP Preview** — real-time Google search result preview
3. **SEO Character Counter** — validates title, description, H1, and alt text lengths
4. **Keyword Density Checker** — analyzes text for top keywords and density %
5. **Website ROI Calculator** — estimates revenue impact of conversion rate improvements
6. **Local Business Schema Generator** — generates JSON-LD structured data markup

---

## SEO Implementation

Every page includes:
- ✅ Unique `<title>` tag (50–60 chars)
- ✅ Meta `description` (120–160 chars)
- ✅ Meta `keywords`
- ✅ `robots: index, follow`
- ✅ Canonical URL (`<link rel="canonical">`)
- ✅ Open Graph tags (og:title, og:description, og:image absolute URL, og:url)
- ✅ Twitter Card tags (including twitter:image)
- ✅ `meta name="theme-color" content="#6222CC"`
- ✅ Schema.org JSON-LD structured data
- ✅ Google site verification tag (index.html)
- ✅ XML sitemap (`sitemap.xml`)
- ✅ Robots.txt with sitemap reference

**Schema types in use:** LocalBusiness, Article, FAQPage, WebPage, BreadcrumbList, ItemList/Product (pricing page)

---

## WebsiteScan Tool

The `WebsiteScan/` directory contains a PHP-based website audit application. It runs independently and performs 50+ SEO, performance, and accessibility checks on any URL.

**Setup requirements:** PHP 8.0+, Composer dependencies installed  
See `WebsiteScan/README.md` for full setup instructions.

---

## Deployment Notes

This is a **static HTML site** with one PHP component (WebsiteScan). The main site can be hosted on any web host that serves static files. The WebsiteScan tool requires PHP and Composer.

**After deploying:**
1. Submit `sitemap.xml` to [Google Search Console](https://search.google.com/search-console)
2. Replace GA4 placeholder (`G-XXXXXXXXXX`) with your actual Measurement ID in all HTML pages
3. Verify your site in Google Search Console using the meta tag already on `index.html`
4. Ensure `robots.txt` is accessible at the root domain

---

## Updating the Site

**Adding a new page:**
1. Copy an existing page as a template
2. Update all meta tags (title, description, canonical URL, OG tags)
3. Add the page to `sitemap.xml`
4. Add a nav link if it should appear in navigation

**Adding a new blog post:**
1. Copy an existing blog post HTML file
2. Update meta tags, canonical URL, schema datePublished, and all content
3. Add a card to `blog/index.html` with appropriate `data-categories`
4. Add the URL to `sitemap.xml`

**Updating content:** Edit the relevant HTML file directly. No build step required.

---

## Analytics

Google Analytics 4 (GA4) tracking code is included on all pages with a placeholder ID.  
**Replace `G-XXXXXXXXXX`** with your actual GA4 Measurement ID from [analytics.google.com](https://analytics.google.com).

---

## Contact & Support

**DBell Creations**  
Fairhope, AL  
📞 251-406-2292  
✉️ dbellcreations@gmail.com  
🌐 [dbellcreations.com](https://www.dbellcreations.com)  
📘 [Facebook](https://www.facebook.com/profile.php?id=100090989871594)  
📷 [Instagram](https://www.instagram.com/dbellcreations/)

---

*Base template: DGital by HTML Codex (htmlcodex.com) — modified extensively for DBell Creations.*
