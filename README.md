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
├── resources.html              — Free Resources (guides, checklists, curated blog links)
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
│   ├── index.html              — Blog listing page (search + filter by category)
│   └── [article].html          — 38 individual blog posts (see Blog section)
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

## Blog Posts (38 articles)

The blog (`blog/index.html`) contains 38+ articles across these categories, with search + filter functionality:

**Web Design:** affordable web design, contractor websites, website redesign checklist, mobile-first design, web design trends, signs you need a redesign, website cost in Alabama, why your site isn't getting leads, website accessibility guide, **7 web design mistakes small businesses make** *(new)*

**SEO:** local SEO guide Alabama, local SEO for service businesses, Google My Business guide, how to get more Google reviews, SEO vs PPC, website speed optimization, how to rank on Google Maps Alabama, **Google Business Profile optimization guide** *(new)*

**Digital Marketing:** social media marketing, content marketing, email marketing, digital marketing budget, lead generation strategies, **how to get your first clients as a new business** *(new)*

**Automation & AI:** business automation intro, workflow automation guide, automate invoicing, AI tools for small business, ChatGPT prompts for small business

**E-commerce & Software:** e-commerce guide Alabama, custom software vs off-the-shelf, best CRM for Alabama businesses

---

## Free Resources Page (`resources.html`)

The site includes a Free Resources page with:

1. **Website & Business Guides** — curated links to in-depth blog posts: Website Launch Checklist, Local SEO Quick-Start Guide, Business Automation Starter, Digital Marketing Budget Guide
2. **Free Website Audit** — launches the WebsiteScan PHP tool (50+ point SEO, speed & accessibility report)
3. **Free Strategy Call** — links to contact page for a complimentary 30-minute consultation
4. **Top Blog Posts by Topic** — organized by Web Design, SEO, and Automation for easy browsing

---

## SEO Implementation

Every page includes:
- ✅ Unique `<title>` tag (50–60 chars)
- ✅ Meta `description` (120–160 chars)
- ✅ Meta `keywords`
- ✅ `robots: index, follow`
- ✅ Canonical URL (`<link rel="canonical">`)
- ✅ Open Graph tags (`og:title`, `og:description`, `og:image` absolute URL, `og:url`, `og:locale`, `og:site_name`)
- ✅ Twitter Card tags (including `twitter:image`)
- ✅ `meta name="theme-color" content="#6222CC"`
- ✅ Schema.org JSON-LD structured data
- ✅ Google site verification tag (index.html)
- ✅ XML sitemap (`sitemap.xml`)
- ✅ Robots.txt with sitemap reference
- ✅ Google Analytics 4 tracking on all pages

**Enhanced schema on homepage (`index.html`):**
- `LocalBusiness` with `aggregateRating` (5-star), `hasOfferCatalog`, `areaServed` (multiple cities), `geo` coordinates, full `OpeningHoursSpecification`

**Schema types in use:** LocalBusiness, Service, Article, FAQPage, WebPage, BreadcrumbList, Blog, ItemList

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

## Future Improvements to Consider

Here are additional features and improvements that could further grow the site:

### Content & SEO
- **More local SEO landing pages** — Target nearby markets: Mobile AL, Gulf Shores AL, Foley AL, Daphne AL, Spanish Fort AL
- **Industry-specific landing pages** — "Web Design for Restaurants," "Web Design for Contractors," "Web Design for Law Firms"
- **Case study detail pages** — Dedicated pages for each portfolio project with measurable results
- **Video content** — Embed YouTube/Loom explainer videos on service pages to boost dwell time
- **Testimonials page** — Dedicated page with full client reviews + review schema markup
- **Press/Media page** — Build authority with any press mentions or awards

### Lead Generation & Conversion
- **Live chat widget** — Tawk.to or Crisp (free options) to capture leads in real time
- **Exit-intent popup** — Offer a free guide download or audit to reduce bounce rate
- **Lead magnet** — A downloadable PDF (e.g., "The Alabama Small Business Website Checklist") in exchange for email signup
- **Email newsletter** — Monthly tips via Mailchimp/ConvertKit to nurture leads
- **Project quote calculator** — Interactive widget estimating website or software project cost

### Technical
- **Web Vitals / Core Web Vitals** — Optimize LCP image with `fetchpriority="high"`, add `width`/`height` to images to prevent layout shift
- **Service Worker / PWA** — Offline support for the main pages
- **Image lazy loading audit** — Ensure all below-fold images have `loading="lazy"` and are served as WebP
- **Preload critical fonts/CSS** — `<link rel="preload">` for style.css and Google Fonts to improve FCP

### Trust & Social Proof
- **Google Reviews widget** — Embed live Google reviews using a free widget (Elfsight or custom API)
- **Client logo strip** — Display logos of businesses you've worked with
- **Before/after slider** — Show website redesign comparisons for social proof

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

