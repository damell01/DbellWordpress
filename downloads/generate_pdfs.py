"""
Generate the 3 downloadable PDFs for DBell Creations.
Run: python3 downloads/generate_pdfs.py
Uses system Arial font for full Unicode support.
"""
from fpdf import FPDF
import os

OUT = os.path.dirname(os.path.abspath(__file__))
FONT_DIR = "C:/Windows/Fonts/"

# Brand colors
PRIMARY   = (98,  34, 204)
SECONDARY = (255, 165,   0)
DARK      = (30,  30,  40)
GRAY      = (90,  90, 100)
LIGHT_BG  = (248, 246, 255)
WHITE     = (255, 255, 255)


class DBellPDF(FPDF):
    def __init__(self, doc_title):
        super().__init__()
        self.doc_title = doc_title
        self.set_margins(18, 18, 18)
        self.set_auto_page_break(auto=True, margin=22)
        # Register Arial as Unicode font
        self.add_font('Arial', '',  FONT_DIR + 'arial.ttf')
        self.add_font('Arial', 'B', FONT_DIR + 'arialbd.ttf')
        self.add_font('Arial', 'I', FONT_DIR + 'ariali.ttf')
        self.add_font('Arial', 'BI', FONT_DIR + 'arialbi.ttf')

    def header(self):
        self.set_fill_color(*PRIMARY)
        self.rect(0, 0, 210, 13, 'F')
        self.set_y(3)
        self.set_font('Arial', 'B', 8)
        self.set_text_color(*WHITE)
        self.cell(0, 7, 'DBELL CREATIONS  \u2022  dbellcreations.com  \u2022  251-406-2292', align='C')
        self.set_text_color(*DARK)
        self.ln(8)

    def footer(self):
        self.set_y(-12)
        self.set_fill_color(*PRIMARY)
        self.rect(0, self.get_y(), 210, 14, 'F')
        self.set_font('Arial', '', 7)
        self.set_text_color(*WHITE)
        self.cell(0, 7, f'\u00a9 2025 DBell Creations  |  {self.doc_title}  |  Page {self.page_no()}', align='C')

    def cover(self, title_lines, tagline):
        self.add_page()
        # Hero band
        self.set_fill_color(*PRIMARY)
        self.rect(0, 13, 210, 76, 'F')
        self.set_y(24)
        self.set_font('Arial', 'B', 20)
        self.set_text_color(*WHITE)
        self.cell(0, 10, 'DBELL CREATIONS', align='C')
        self.ln(13)
        self.set_font('Arial', 'B', 14)
        self.set_text_color(255, 220, 80)
        for line in title_lines:
            self.cell(0, 9, line, align='C')
            self.ln(9)
        self.ln(3)
        self.set_font('Arial', 'I', 9)
        self.set_text_color(210, 195, 255)
        self.multi_cell(0, 6, tagline, align='C')

        # Info box
        self.set_y(100)
        self.set_fill_color(*LIGHT_BG)
        self.set_draw_color(*PRIMARY)
        self.set_line_width(0.4)
        self.rect(18, 100, 174, 28, 'FD')
        self.set_y(107)
        self.set_font('Arial', 'I', 9)
        self.set_text_color(*GRAY)
        self.multi_cell(0, 6,
            'Free resource from DBell Creations \u2014 web design, SEO, software & automation agency\n'
            'based in Fairhope, AL. Helping Alabama small businesses grow online.',
            align='C')
        self.set_text_color(*DARK)

    def section(self, text):
        self.ln(5)
        self.set_fill_color(*PRIMARY)
        self.set_text_color(*WHITE)
        self.set_font('Arial', 'B', 10)
        self.cell(0, 8, '  ' + text, fill=True, ln=True)
        self.ln(2)
        self.set_text_color(*DARK)

    def item(self, text, indent=0):
        self.set_font('Arial', '', 9.5)
        self.set_text_color(*DARK)
        x = self.l_margin + indent
        y = self.get_y()
        # Checkbox square
        self.set_draw_color(*PRIMARY)
        self.set_line_width(0.3)
        self.rect(x, y + 1.5, 3.5, 3.5)
        self.set_xy(x + 6, y)
        self.multi_cell(0, 5.5, text)
        self.ln(1)

    def numbered(self, num, title, body):
        self.set_font('Arial', 'B', 10)
        self.set_text_color(*PRIMARY)
        self.cell(8, 6, f'{num}.', ln=False)
        self.set_text_color(*DARK)
        self.multi_cell(0, 6, title)
        if body:
            self.set_font('Arial', 'I', 9)
            self.set_text_color(*GRAY)
            self.set_x(self.l_margin + 8)
            self.multi_cell(0, 5, body)
        self.ln(2)

    def tip(self, text):
        self.ln(3)
        lx = self.l_margin
        ly = self.get_y()
        # Accent bar
        self.set_fill_color(*SECONDARY)
        self.rect(lx, ly, 2.5, 16, 'F')
        self.set_fill_color(*LIGHT_BG)
        self.rect(lx + 2.5, ly, 171.5, 16, 'F')
        self.set_xy(lx + 7, ly + 3)
        self.set_font('Arial', 'BI', 8.5)
        self.set_text_color(*GRAY)
        self.multi_cell(0, 5, 'Pro Tip:  ' + text)
        self.set_y(ly + 19)
        self.set_text_color(*DARK)

    def cta_box(self):
        self.ln(6)
        y = self.get_y()
        self.set_fill_color(*PRIMARY)
        self.rect(18, y, 174, 26, 'F')
        self.set_y(y + 5)
        self.set_font('Arial', 'B', 10)
        self.set_text_color(*WHITE)
        self.cell(0, 7, 'Want a free website mockup? We\'ll build one \u2014 no commitment.', align='C', ln=True)
        self.set_font('Arial', '', 8.5)
        self.cell(0, 6, 'dbellcreations.com/free-mockup.html  |  Call: 251-406-2292', align='C', ln=True)
        self.set_text_color(*DARK)


# ================================================================
# PDF 1 \u2014 Website Launch Checklist
# ================================================================
def make_launch_checklist():
    pdf = DBellPDF('Website Launch Checklist')
    pdf.cover(
        ['Website Launch Checklist'],
        'Everything to verify before going live \u2014 so nothing slips through the cracks.'
    )

    pdf.add_page()
    pdf.set_y(20)

    pdf.section('1. Content & Copy')
    for t in [
        'All placeholder text (Lorem ipsum) replaced with real content',
        'Business name, address, and phone number are correct on every page',
        'Contact form sends to the right email address \u2014 test it',
        'All images have descriptive alt text for SEO and accessibility',
        'No spelling errors \u2014 run a final proofread on every page',
        'Privacy Policy and Terms of Service pages are live and linked in the footer',
    ]:
        pdf.item(t)

    pdf.section('2. Design & Mobile')
    for t in [
        'Site looks correct on desktop, tablet, and mobile (test all three)',
        'Navigation menu works on mobile \u2014 hamburger menu opens and closes',
        'All buttons and links are tappable on mobile (min. 44 px touch targets)',
        'Images are not stretched, blurry, or cut off on any screen size',
        'Fonts load correctly and are readable at all sizes',
        'No horizontal scroll bar on mobile',
    ]:
        pdf.item(t)

    pdf.section('3. Performance & Speed')
    for t in [
        'Images are compressed and correctly sized (use WebP where possible)',
        'Page loads in under 3 seconds on mobile \u2014 test with Google PageSpeed',
        'No broken images or 404 errors on any page',
        'CSS and JavaScript files are minified for production',
        'Favicon is set and shows in the browser tab',
    ]:
        pdf.item(t)

    pdf.add_page()
    pdf.set_y(20)

    pdf.section('4. SEO Basics')
    for t in [
        'Each page has a unique, descriptive <title> tag (under 60 characters)',
        'Each page has a unique meta description (under 160 characters)',
        'H1 tag is present on every page \u2014 only one H1 per page',
        'Google Analytics (GA4) is installed and tracking',
        'Google Search Console is set up and site is submitted for indexing',
        'XML sitemap is generated and submitted to Google Search Console',
        'robots.txt file is in place and not blocking important pages',
        'Canonical tags are set correctly to avoid duplicate content',
    ]:
        pdf.item(t)

    pdf.section('5. Technical & Security')
    for t in [
        'SSL certificate is active \u2014 URL shows https:// (not http://)',
        'Site redirects www to non-www (or vice versa) \u2014 pick one and stick with it',
        'Old domain (if redesign) redirects to new domain with 301 redirects',
        'Contact form has spam protection (CAPTCHA or honeypot)',
        'All third-party scripts load correctly (chat, analytics, pixels)',
        'Backup system is in place before going live',
    ]:
        pdf.item(t)

    pdf.section('6. Local Business Extras')
    for t in [
        'Google Business Profile is claimed, verified, and up to date',
        'NAP (Name, Address, Phone) matches exactly across your site and GBP',
        'Business hours are correct on the website',
        'Location/service area pages are published if needed',
        'Schema markup (LocalBusiness) is added for better Google visibility',
    ]:
        pdf.item(t)

    pdf.tip('Run your homepage through Google PageSpeed Insights and GTmetrix before launch. Fix anything scoring below 80.')
    pdf.cta_box()

    out = os.path.join(OUT, 'website-launch-checklist.pdf')
    pdf.output(out)
    print(f'Created: {out}')


# ================================================================
# PDF 2 \u2014 Local SEO Guide for Alabama
# ================================================================
def make_seo_guide():
    pdf = DBellPDF('Local SEO Guide for Alabama')
    pdf.cover(
        ['Local SEO Guide', 'for Alabama Small Businesses'],
        'Step-by-step strategies to rank higher on Google Maps\nand attract more local customers.'
    )

    pdf.add_page()
    pdf.set_y(20)
    pdf.set_font('Arial', '', 9.5)
    pdf.set_text_color(*GRAY)
    pdf.multi_cell(0, 6,
        'Local SEO is the process of optimizing your online presence so people in your area find you '
        'when they search for your services. For most Alabama small businesses, local SEO is the '
        'highest-ROI marketing investment you can make.')
    pdf.ln(4)

    pdf.section('Step 1 \u2014 Claim & Optimize Your Google Business Profile')
    for n, title, body in [
        (1, 'Claim your listing', 'Go to business.google.com and claim or create your profile. Verify by postcard or phone.'),
        (2, 'Fill out every field', 'Name, category, address, phone, website, hours, description \u2014 leave nothing blank.'),
        (3, 'Choose the right primary category', 'The most important field. Pick the most specific category that describes your core service.'),
        (4, 'Add photos weekly', 'Profiles with photos get 42% more direction requests. Add interior, exterior, team, and work photos.'),
        (5, 'Post weekly updates', 'Use the Posts feature to share offers, news, or tips. Google rewards active profiles.'),
    ]:
        pdf.numbered(n, title, body)

    pdf.section('Step 2 \u2014 Build Consistent Citations')
    pdf.set_font('Arial', '', 9.5)
    pdf.set_text_color(*DARK)
    pdf.multi_cell(0, 6,
        'A citation is any mention of your business name, address, and phone number (NAP) on another '
        'website. Consistency matters \u2014 even small differences (St. vs Street) can hurt rankings.')
    pdf.ln(3)
    for t in [
        'Submit to Yelp, Bing Places, Apple Maps, and Yellow Pages',
        'List on Alabama-specific directories (Alabama.com, Chamber of Commerce sites)',
        'Ensure NAP is exactly the same on every listing \u2014 including your website',
        'Use a tool like BrightLocal or Moz Local to audit your existing citations',
    ]:
        pdf.item(t)

    pdf.add_page()
    pdf.set_y(20)

    pdf.section('Step 3 \u2014 On-Page SEO for Local Rankings')
    for n, title, body in [
        (1, 'Create location pages', 'Build a dedicated page for each city/area you serve. Example: "Web Design Fairhope AL".'),
        (2, 'Use local keywords naturally', 'Include your city and county in page titles, H1s, and body copy \u2014 but write for humans first.'),
        (3, 'Embed a Google Map', 'Add an embedded map on your Contact page. It signals location relevance to Google.'),
        (4, 'Add LocalBusiness schema markup', 'Structured data helps Google understand your location, hours, and services.'),
        (5, 'NAP on every page', 'Put your full address and phone number in the footer of every page on your site.'),
    ]:
        pdf.numbered(n, title, body)

    pdf.section('Step 4 \u2014 Get More Google Reviews')
    pdf.set_font('Arial', '', 9.5)
    pdf.set_text_color(*DARK)
    pdf.multi_cell(0, 6,
        'Reviews are one of the top local ranking factors. Businesses with more recent, positive '
        'reviews consistently outrank competitors with fewer or older reviews.')
    pdf.ln(3)
    for t in [
        'Ask every satisfied customer directly \u2014 timing matters (ask right after a win)',
        'Create a short link to your Google review page and send it via text or email',
        'Respond to every review \u2014 positive and negative \u2014 professionally',
        'Aim for 5+ new reviews per month to stay ahead of competitors',
    ]:
        pdf.item(t)

    pdf.section('Step 5 \u2014 Build Local Links')
    for t in [
        'Join your local Chamber of Commerce (they usually link to members)',
        'Sponsor local events, sports teams, or community organizations',
        'Get featured in local news or Alabama business blogs',
        'Partner with complementary local businesses for cross-promotion',
        'Submit to local business associations in Baldwin County and Mobile County',
    ]:
        pdf.item(t)

    pdf.tip('Baldwin County and Mobile County are competitive. Target hyper-local neighborhoods (Fairhope, Daphne, Spanish Fort, Gulf Shores) with dedicated pages.')

    pdf.section('Quick Reference Checklist')
    for t in [
        'Google Business Profile claimed and fully completed',
        'NAP consistent across all online listings',
        'At least 10 Google reviews (aim for 25+)',
        'Location pages for each city/area you serve',
        'LocalBusiness schema markup installed',
        'Google Search Console set up and tracking',
        'Embedded Google Map on Contact page',
        'Active posting on Google Business Profile (weekly)',
    ]:
        pdf.item(t)

    pdf.cta_box()

    out = os.path.join(OUT, 'local-seo-guide-alabama.pdf')
    pdf.output(out)
    print(f'Created: {out}')


# ================================================================
# PDF 3 \u2014 Content Marketing Checklist
# ================================================================
def make_content_checklist():
    pdf = DBellPDF('Content Marketing Checklist')
    pdf.cover(
        ['Content Marketing Checklist', 'for Local Businesses'],
        'A practical system for creating content that attracts\nlocal customers and builds trust online.'
    )

    pdf.add_page()
    pdf.set_y(20)
    pdf.set_font('Arial', '', 9.5)
    pdf.set_text_color(*GRAY)
    pdf.multi_cell(0, 6,
        'Content marketing is not about posting random things online. It\'s a system for consistently '
        'creating content that answers your customers\' questions, demonstrates your expertise, and keeps '
        'your business top-of-mind. Use this checklist to build that system.')
    pdf.ln(4)

    pdf.section('Before You Create \u2014 Strategy First')
    for t in [
        'Define your target customer: Who are they? What do they search for? What are their biggest pain points?',
        'List the top 10 questions your customers ask before hiring you',
        'Research local keywords your ideal customers use (Google Autocomplete is a free starting point)',
        'Choose your content pillars: 2\u20134 topics you will consistently create content around',
        'Choose your primary platform: blog, social media, email, or video (pick one to start)',
    ]:
        pdf.item(t)

    pdf.section('Blog Post Checklist (Per Article)')
    for t in [
        'Keyword is in the title, URL slug, first paragraph, and at least one H2',
        'Title is compelling \u2014 answers a question or promises a specific result',
        'Post is at least 800 words (aim for 1,200+ for competitive topics)',
        'Includes at least one internal link to another page on your site',
        'Includes at least one external link to a credible, authoritative source',
        'Has a clear call-to-action at the end (contact us, get a quote, download, etc.)',
        'Featured image is added and has descriptive alt text',
        'Meta description is written (under 160 characters)',
    ]:
        pdf.item(t)

    pdf.add_page()
    pdf.set_y(20)

    pdf.section('Social Media Checklist (Weekly)')
    for t in [
        'Post at least 3x per week on your primary platform',
        'Mix content types: educational, behind-the-scenes, testimonials, offers (roughly 3:1:1:1)',
        'Every post has a visual \u2014 photo, graphic, or short video',
        'Use 3\u20135 relevant local hashtags on Instagram/Facebook',
        'Respond to all comments and DMs within 24 hours',
        'Share your blog posts and repurpose them as social content',
        'Track which posts get the most engagement and create more of that type',
    ]:
        pdf.item(t)

    pdf.section('Email Marketing Checklist (Monthly)')
    for t in [
        'Send at least one email per month to your list',
        'Subject line is under 50 characters and creates curiosity or urgency',
        'Email leads with value (tip, guide, update) before any promotion',
        'Single clear call-to-action button per email',
        'Preview text is set (the line that shows next to the subject line in the inbox)',
        'Test the email on mobile before sending \u2014 most opens happen on phones',
        'Segment your list if possible (leads vs. customers vs. past customers)',
    ]:
        pdf.item(t)

    pdf.section('Monthly Content Calendar Template')
    for n, title, body in [
        (1, 'Week 1', 'Publish one blog post targeting a local keyword. Share on social 3x.'),
        (2, 'Week 2', 'Share a client result, case study, or testimonial. Post behind-the-scenes content.'),
        (3, 'Week 3', 'Publish a how-to or educational post. Email your list a value-packed tip.'),
        (4, 'Week 4', 'Share an offer, seasonal content, or community spotlight. Review your analytics.'),
    ]:
        pdf.numbered(n, title, body)

    pdf.section('Measuring What Works')
    for t in [
        'Check Google Analytics monthly: which pages get the most traffic?',
        'Track which blog posts drive contact form submissions or calls',
        'Monitor social engagement rate (likes + comments + shares \u00f7 reach)',
        'Track email open rate (industry average ~20% \u2014 aim higher)',
        'Ask new customers "how did you find us?" and track it in a spreadsheet',
    ]:
        pdf.item(t)

    pdf.tip('Local content beats generic content every time. Write about your community, local events, and specific cities you serve. "Web Design for Fairhope Contractors" outperforms "Web Design Tips" for local search.')

    pdf.cta_box()

    out = os.path.join(OUT, 'content-marketing-checklist.pdf')
    pdf.output(out)
    print(f'Created: {out}')


if __name__ == '__main__':
    make_launch_checklist()
    make_seo_guide()
    make_content_checklist()
    print('\nAll 3 PDFs generated successfully.')
