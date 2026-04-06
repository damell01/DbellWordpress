<?php
namespace App\Controllers;

use App\Core\{Request, Session};

class HomeController extends BaseController {
    public function index(Request $request): void {
        $this->view('home', [
            'title'       => $this->settings->get('site_name', 'VerityScan') . ' - Free Website Audit',
            'headline'    => $this->settings->get('hero_headline', 'Find Out What\'s Holding Your Website Back'),
            'subheadline' => $this->settings->get('hero_subheadline', 'Get a free, instant audit of your website - SEO, accessibility, performance, and conversion issues revealed in seconds.'),
        ]);
    }

    public function features(Request $request): void {
        $this->view('features', ['title' => 'Features - ' . $this->settings->get('site_name', 'VerityScan')]);
    }

    public function about(Request $request): void {
        $this->view('about', ['title' => 'About - ' . $this->settings->get('site_name', 'VerityScan')]);
    }

    public function contact(Request $request): void {
        if ($request->isPost()) {
            $this->handleContact($request);
            return;
        }

        $this->view('contact', ['title' => 'Contact Us']);
    }

    private function handleContact(Request $request): void {
        $redirectTo = $this->contactRedirectTarget($request);
        $errors = $request->validate([
            'name'        => 'required|max:100',
            'email'       => 'required|email',
            'website_url' => 'url',
            'message'     => 'required|max:2000',
        ]);

        if ($errors) {
            Session::setFlash('errors', $errors);
            Session::setFlash('_old', $request->all());
            $this->redirect($redirectTo);
            return;
        }

        $db = \App\Core\Database::getInstance();
        $leadId = $this->syncContactLead($db, $request);

        $db->insert('contact_requests', [
            'lead_id'      => $leadId,
            'name'         => $request->post('name'),
            'email'        => $request->post('email'),
            'phone'        => $request->post('phone', ''),
            'company'      => $request->post('company', ''),
            'message'      => $request->post('message'),
            'service_type' => $request->post('service_type', ''),
            'source'       => 'websitescan',
            'website_url'  => $request->post('website_url', ''),
            'status'       => 'new',
            'created_at'   => date('Y-m-d H:i:s'),
        ]);

        $mailer = new \App\Services\MailService();
        $adminSent = $mailer->notifyAdminContactRequest($request->all());

        if ($adminSent) {
            Session::setFlash('success', 'Thank you! Your message was sent and our team was notified by email.');
        } else {
            $detail = $mailer->getLastError();
            Session::setFlash(
                'error',
                'Your message was saved, but notification email delivery failed.' . ($detail !== '' ? ' ' . $detail : '')
            );
        }

        $this->redirect($redirectTo);
    }

    public function privacy(Request $request): void {
        $this->view('privacy', ['title' => 'Privacy Policy']);
    }

    public function terms(Request $request): void {
        $this->view('terms', ['title' => 'Terms of Service']);
    }

    public function fixMyWebsite(Request $request): void {
        $this->view('fix-my-website', ['title' => 'Fix My Website - Get Professional Help']);
    }

    private function contactRedirectTarget(Request $request): string {
        $target = trim((string) $request->post('redirect_to', 'contact'));
        return str_starts_with($target, 'fix-my-website') ? $target : 'contact';
    }

    private function syncContactLead(\App\Core\Database $db, Request $request): ?int {
        $email = trim((string) $request->post('email', ''));
        $websiteUrl = trim((string) $request->post('website_url', ''));

        $lead = null;
        if ($email !== '') {
            $lead = $db->fetch("SELECT * FROM leads WHERE email = ? ORDER BY id DESC LIMIT 1", [$email]);
        }
        if (!$lead && $websiteUrl !== '') {
            $lead = $db->fetch("SELECT * FROM leads WHERE website_url = ? ORDER BY id DESC LIMIT 1", [$websiteUrl]);
        }

        $leadData = [
            'website_url'   => $websiteUrl,
            'contact_name'  => trim((string) $request->post('name', '')),
            'email'         => $email,
            'phone'         => trim((string) $request->post('phone', '')),
            'business_name' => trim((string) $request->post('company', '')),
            'notes'         => trim((string) $request->post('message', '')),
        ];

        if ($lead) {
            $db->update('leads', [
                'website_url'   => $leadData['website_url'] !== '' ? $leadData['website_url'] : ($lead['website_url'] ?? ''),
                'contact_name'  => $leadData['contact_name'] !== '' ? $leadData['contact_name'] : ($lead['contact_name'] ?? ''),
                'email'         => $leadData['email'] !== '' ? $leadData['email'] : ($lead['email'] ?? ''),
                'phone'         => $leadData['phone'] !== '' ? $leadData['phone'] : ($lead['phone'] ?? ''),
                'business_name' => $leadData['business_name'] !== '' ? $leadData['business_name'] : ($lead['business_name'] ?? ''),
                'notes'         => !empty($lead['notes']) ? $lead['notes'] : $leadData['notes'],
            ], ['id' => $lead['id']]);

            return (int) $lead['id'];
        }

        return $db->insert('leads', [
            'website_url'   => $leadData['website_url'],
            'business_name' => $leadData['business_name'],
            'contact_name'  => $leadData['contact_name'],
            'email'         => $leadData['email'],
            'phone'         => $leadData['phone'],
            'notes'         => $leadData['notes'],
            'source'        => 'contact_form',
            'status'        => 'new',
            'created_at'    => date('Y-m-d H:i:s'),
        ]);
    }
}
