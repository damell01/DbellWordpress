<?php
// routes.php - Application routes

use App\Core\Router;

$router = new Router();

// ─── Public Routes ───────────────────────────────────────────────────────────
$router->get('/',                'HomeController@index');
$router->get('/features',        'HomeController@features');
$router->get('/about',           'HomeController@about');
$router->get('/contact',         'HomeController@contact');
$router->post('/contact',        'HomeController@contact',        ['Csrf']);
$router->get('/privacy',         'HomeController@privacy');
$router->get('/terms',           'HomeController@terms');
$router->get('/fix-my-website',  'HomeController@fixMyWebsite');

// ─── Audit Routes ─────────────────────────────────────────────────────────────
$router->get('/audit',                    'AuditController@form');
$router->post('/audit',                   'AuditController@submit',      ['Csrf']);
$router->get('/report/{token}',           'AuditController@report');
$router->post('/report/{token}/help',     'AuditController@requestHelp', ['Csrf']);
$router->post('/report/{token}/feedback', 'AuditController@submitFeedback', ['Csrf']);

// ─── Auth Routes ─────────────────────────────────────────────────────────────
$router->get('/admin/login',     'AuthController@loginForm');
$router->post('/admin/login',    'AuthController@login',          ['Csrf']);
$router->get('/admin/logout',    'AuthController@logout');

// ─── Admin Routes ─────────────────────────────────────────────────────────────
$router->get('/admin',                    'AdminController@dashboard',    ['Admin']);
$router->get('/admin/leads',              'AdminController@leads',        ['Admin']);
$router->get('/admin/leads/{id}',         'AdminController@viewLead',     ['Admin']);
$router->post('/admin/leads/{id}',        'AdminController@updateLead',   ['Admin', 'Csrf']);
$router->post('/admin/leads/{id}/note',         'AdminController@addLeadNote',    ['Admin', 'Csrf']);
$router->post('/admin/leads/{id}/send-message', 'AdminController@sendLeadMessage', ['Admin', 'Csrf']);
$router->get('/admin/scans',              'AdminController@scans',        ['Admin']);
$router->get('/admin/contacts',           'AdminController@contacts',           ['Admin']);
$router->get('/admin/contacts/{id}',      'AdminController@viewContact',         ['Admin']);
$router->post('/admin/contacts/{id}',     'AdminController@updateContactStatus', ['Admin', 'Csrf']);
$router->post('/admin/contacts/{id}/send-message', 'AdminController@sendContactMessage', ['Admin', 'Csrf']);
$router->get('/admin/settings',           'AdminController@settings',     ['Admin']);
$router->post('/admin/settings',          'AdminController@settings',     ['Admin', 'Csrf']);
$router->post('/admin/settings/test-email','AdminController@sendTestEmail',['Admin', 'Csrf']);
$router->get('/admin/schema-upgrade',     'AdminController@schemaUpgrade',['Admin']);
$router->get('/admin/export/leads',       'AdminController@exportLeads',  ['Admin']);
$router->get('/admin/export/scans',       'AdminController@exportScans',  ['Admin']);
$router->get('/admin/export/contacts',    'AdminController@exportContacts',['Admin']);

return $router;
