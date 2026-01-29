<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\File;

class GenerateFeaturesPDF extends Command
{
    protected $signature = 'features:generate-pdf {--lang=en : Language for the PDF (en or fr)}';
    protected $description = 'Generate a PDF document listing all implemented features';

    public function handle()
    {
        $lang = $this->option('lang') ?? 'en';
        
        if (!in_array($lang, ['en', 'fr'])) {
            $this->error('Invalid language. Use "en" or "fr"');
            return 1;
        }

        $this->info("Generating features PDF in {$lang}...");

        $html = $this->generateHTML($lang);

        $pdf = Pdf::loadHTML($html);
        $pdf->setPaper('a4', 'portrait');
        
        $langSuffix = $lang === 'fr' ? '-fr' : '';
        $filename = 'features-implementation' . $langSuffix . '-' . date('Y-m-d') . '.pdf';
        $path = storage_path('app/public/' . $filename);
        
        // Ensure directory exists
        File::ensureDirectoryExists(storage_path('app/public'));
        
        $pdf->save($path);
        
        $this->info("PDF generated successfully: {$filename}");
        $this->info("Location: storage/app/public/{$filename}");
        $this->info("Public URL: " . asset("storage/{$filename}"));

        return 0;
    }

    private function generateHTML(string $lang = 'en'): string
    {
        $content = $lang === 'fr' ? $this->getFrenchContent() : $this->getEnglishContent();
        
        return $this->getHTMLTemplate($content, $lang);
    }

    private function getHTMLTemplate(array $content, string $lang): string
    {
        $date = $lang === 'fr' 
            ? strftime('%d %B %Y', strtotime('today'))
            : date('F d, Y');
        
        return '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 15px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #00adf1;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .header h1 {
            color: #00adf1;
            margin: 0 0 5px 0;
            font-size: 20pt;
        }
        .header p {
            color: #666;
            margin: 2px 0;
            font-size: 9pt;
        }
        .section {
            margin-bottom: 15px;
            page-break-inside: avoid;
        }
        .section-title {
            background-color: #00adf1;
            color: white;
            padding: 6px 12px;
            margin: 12px 0 8px 0;
            font-size: 12pt;
            font-weight: bold;
            border-radius: 3px;
        }
        .subsection {
            margin-left: 15px;
            margin-bottom: 10px;
        }
        .subsection-title {
            font-weight: bold;
            color: #00adf1;
            font-size: 11pt;
            margin: 10px 0 5px 0;
        }
        .feature-list {
            margin-left: 20px;
            margin-bottom: 8px;
        }
        .feature-item {
            margin-bottom: 4px;
            padding-left: 3px;
            font-size: 9.5pt;
        }
        .tech-stack {
            background-color: #f8f9fa;
            padding: 10px;
            border-left: 4px solid #00adf1;
            margin: 12px 0;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            text-align: center;
            color: #666;
            font-size: 9pt;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        table th, table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        table th {
            background-color: #00adf1;
            color: white;
        }
        .note {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 8px;
            margin: 8px 0;
            font-size: 9pt;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>' . htmlspecialchars($content['title']) . '</h1>
        <p>' . htmlspecialchars($content['subtitle']) . '</p>
        <p>' . ($lang === 'fr' ? 'Généré le' : 'Generated') . ': ' . $date . '</p>
    </div>

    ' . $content['sections'] . '

    <div class="tech-stack">
        <h3 style="margin-top: 0; color: #00adf1;">' . htmlspecialchars($content['tech_title']) . '</h3>
        <table>
            <tr>
                <th>' . htmlspecialchars($content['tech_table']['tech']) . '</th>
                <th>' . htmlspecialchars($content['tech_table']['version']) . '</th>
            </tr>
            <tr>
                <td>PHP</td>
                <td>8.2+</td>
            </tr>
            <tr>
                <td>Laravel Framework</td>
                <td>12.0</td>
            </tr>
            <tr>
                <td>Laravel Sanctum</td>
                <td>4.2</td>
            </tr>
            <tr>
                <td>Laravel Breeze</td>
                <td>2.3</td>
            </tr>
            <tr>
                <td>Livewire</td>
                <td>3.7</td>
            </tr>
            <tr>
                <td>DomPDF</td>
                <td>3.1</td>
            </tr>
            <tr>
                <td>Maatwebsite Excel</td>
                <td>3.1</td>
            </tr>
            <tr>
                <td>TailwindCSS</td>
                <td>Latest</td>
            </tr>
            <tr>
                <td>Shadcn UI</td>
                <td>1.1</td>
            </tr>
            <tr>
                <td>React</td>
                <td>18.2+</td>
            </tr>
            <tr>
                <td>React Router DOM</td>
                <td>6.20+</td>
            </tr>
            <tr>
                <td>Vite</td>
                <td>5.0+</td>
            </tr>
            <tr>
                <td>Axios</td>
                <td>Latest</td>
            </tr>
            <tr>
                <td>DOMPurify</td>
                <td>Latest</td>
            </tr>
            <tr>
                <td>React Helmet Async</td>
                <td>Latest</td>
            </tr>
            <tr>
                <td>Embla Carousel</td>
                <td>Latest</td>
            </tr>
        </table>
    </div>
</body>
</html>';
    }

    private function featureItem(string $text): string
    {
        return '<div class="feature-item">- ' . htmlspecialchars($text) . '</div>';
    }

    private function getEnglishContent(): array
    {
        return [
            'title' => 'Backend & Frontend Features Implementation Report',
            'subtitle' => 'Comprehensive List of All Implemented Features',
            'tech_title' => 'Technology Stack',
            'tech_table' => [
                'tech' => 'Technology',
                'version' => 'Version/Package',
            ],
            'note' => 'This document lists all features implemented in the backend system. The frontend team should reference this document to understand what backend capabilities are available for integration. All features are production-ready and fully tested.',
            'sections' => $this->getEnglishSections(),
        ];
    }

    private function getFrenchContent(): array
    {
        return [
            'title' => 'Rapport d\'Implémentation des Fonctionnalités Backend & Frontend',
            'subtitle' => 'Liste Complète de Toutes les Fonctionnalités Implémentées',
            'tech_title' => 'Stack Technologique',
            'tech_table' => [
                'tech' => 'Technologie',
                'version' => 'Version/Package',
            ],
            'note' => 'Ce document liste toutes les fonctionnalités implémentées dans le système backend. L\'équipe frontend doit consulter ce document pour comprendre quelles capacités backend sont disponibles pour l\'intégration. Toutes les fonctionnalités sont prêtes pour la production et entièrement testées.',
            'sections' => $this->getFrenchSections(),
        ];
    }

    private function getEnglishSections(): string
    {
        return '
    <div class="section">
        <div class="section-title">1. Authentication & User Management</div>
        <div class="subsection">
            <div class="subsection-title">Authentication System</div>
            <div class="feature-list">
                ' . $this->featureItem('Laravel Sanctum token-based authentication for API') . '
                ' . $this->featureItem('Laravel Breeze session-based authentication for admin dashboard') . '
                ' . $this->featureItem('User registration with automatic wallet creation') . '
                ' . $this->featureItem('User login with token generation') . '
                ' . $this->featureItem('Password validation (min 8 chars, uppercase, lowercase, number)') . '
                ' . $this->featureItem('Password hashing with bcrypt') . '
                ' . $this->featureItem('Token management (max 5 tokens per user, auto-cleanup)') . '
                ' . $this->featureItem('User logout functionality') . '
                ' . $this->featureItem('User profile update') . '
                ' . $this->featureItem('Password change functionality') . '
            </div>
        </div>
        <div class="subsection">
            <div class="subsection-title">Role-Based Access Control (RBAC)</div>
            <div class="feature-list">
                ' . $this->featureItem('Super Admin role with full system access') . '
                ' . $this->featureItem('Admin role with configurable permissions') . '
                ' . $this->featureItem('Organizer role for event management') . '
                ' . $this->featureItem('Regular user role for bookings') . '
                ' . $this->featureItem('Resource-based permissions system') . '
                ' . $this->featureItem('Permission checking middleware') . '
                ' . $this->featureItem('Admin impersonation feature (super-admin only)') . '
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">2. Event & Accommodation Management</div>
        <div class="subsection">
            <div class="subsection-title">Event Management</div>
            <div class="feature-list">
                ' . $this->featureItem('Create, read, update, delete events (accommodations)') . '
                ' . $this->featureItem('Event slug generation and management') . '
                ' . $this->featureItem('Event logo and banner image uploads') . '
                ' . $this->featureItem('Event description and details') . '
                ' . $this->featureItem('Event status management (active/inactive)') . '
                ' . $this->featureItem('Event duplication functionality') . '
                ' . $this->featureItem('Organizer assignment to events') . '
                ' . $this->featureItem('Commission percentage configuration per event') . '
                ' . $this->featureItem('Event menu links configuration (JSON)') . '
            </div>
        </div>
        <div class="subsection">
            <div class="subsection-title">Event Content Management</div>
            <div class="feature-list">
                ' . $this->featureItem('Custom content pages (Conditions, Info, FAQ)') . '
                ' . $this->featureItem('Rich text content editing') . '
                ' . $this->featureItem('Content per event/type management') . '
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">3. Hotel Management</div>
        <div class="subsection">
            <div class="subsection-title">Hotel Operations</div>
            <div class="feature-list">
                ' . $this->featureItem('Create, read, update, delete hotels') . '
                ' . $this->featureItem('Hotel slug generation') . '
                ' . $this->featureItem('Hotel location and description') . '
                ' . $this->featureItem('Hotel status management') . '
                ' . $this->featureItem('Hotel duplication functionality') . '
                ' . $this->featureItem('Hotel association with events') . '
            </div>
        </div>
        <div class="subsection">
            <div class="subsection-title">Hotel Images Management</div>
            <div class="feature-list">
                ' . $this->featureItem('Multiple image uploads per hotel') . '
                ' . $this->featureItem('Image reordering (drag & drop)') . '
                ' . $this->featureItem('Image update and deletion') . '
                ' . $this->featureItem('Livewire-powered image manager') . '
                ' . $this->featureItem('Image storage with dual storage service') . '
            </div>
        </div>
        <div class="subsection">
            <div class="subsection-title">Hotel Packages</div>
            <div class="feature-list">
                ' . $this->featureItem('Package creation and management') . '
                ' . $this->featureItem('Package pricing (HT and TTC)') . '
                ' . $this->featureItem('Room type configuration') . '
                ' . $this->featureItem('Package availability management') . '
                ' . $this->featureItem('Package duplication') . '
                ' . $this->featureItem('Package association with hotels') . '
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">4. Booking System</div>
        <div class="subsection">
            <div class="subsection-title">Booking Management</div>
            <div class="feature-list">
                ' . $this->featureItem('Create bookings via API (authenticated users)') . '
                ' . $this->featureItem('Automatic booking reference generation') . '
                ' . $this->featureItem('Booking status management (pending, confirmed, paid, cancelled, refunded)') . '
                ' . $this->featureItem('Guest information capture (name, email, phone, company)') . '
                ' . $this->featureItem('Check-in and check-out date management') . '
                ' . $this->featureItem('Guests count tracking') . '
                ' . $this->featureItem('Resident names (up to 2 residents)') . '
                ' . $this->featureItem('Special instructions/requests field') . '
                ' . $this->featureItem('Flight information in bookings') . '
                ' . $this->featureItem('Booking search and filtering') . '
                ' . $this->featureItem('Booking details view with expandable rows') . '
            </div>
        </div>
        <div class="subsection">
            <div class="subsection-title">Payment System</div>
            <div class="feature-list">
                ' . $this->featureItem('Payment method selection (wallet, bank transfer, both)') . '
                ' . $this->featureItem('Wallet payment integration') . '
                ' . $this->featureItem('Bank transfer payment support') . '
                ' . $this->featureItem('Mixed payment (wallet + bank)') . '
                ' . $this->featureItem('Payment document upload') . '
                ' . $this->featureItem('Flight ticket upload') . '
                ' . $this->featureItem('Automatic booking confirmation for wallet payments') . '
                ' . $this->featureItem('Payment amount tracking (wallet_amount, bank_amount)') . '
            </div>
        </div>
        <div class="subsection">
            <div class="subsection-title">Refund System</div>
            <div class="feature-list">
                ' . $this->featureItem('Booking refund processing') . '
                ' . $this->featureItem('Automatic wallet credit on refund') . '
                ' . $this->featureItem('Refund amount tracking') . '
                ' . $this->featureItem('Refund notes/remarks') . '
                ' . $this->featureItem('Automatic room availability restoration on refund') . '
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">5. Flight Management System</div>
        <div class="subsection">
            <div class="subsection-title">Flight Booking Management</div>
            <div class="feature-list">
                ' . $this->featureItem('Flight creation and management') . '
                ' . $this->featureItem('Automatic flight reference generation') . '
                ' . $this->featureItem('Flight class selection (economy, business, first)') . '
                ' . $this->featureItem('Flight category (one-way, round-trip)') . '
                ' . $this->featureItem('Departure flight details (date, time, number, airports, price)') . '
                ' . $this->featureItem('Return flight details (for round-trip)') . '
                ' . $this->featureItem('eTicket upload and management') . '
                ' . $this->featureItem('eTicket number and reference tracking') . '
                ' . $this->featureItem('Beneficiary type (organizer or client)') . '
                ' . $this->featureItem('Automatic client account creation for flight bookings') . '
                ' . $this->featureItem('Flight credentials PDF generation') . '
                ' . $this->featureItem('Flight credentials email sending') . '
                ' . $this->featureItem('Flight status management (pending, paid)') . '
                ' . $this->featureItem('Flight payment method tracking') . '
            </div>
        </div>
        <div class="subsection">
            <div class="subsection-title">Flight Price Visibility</div>
            <div class="feature-list">
                ' . $this->featureItem('Public flight price visibility toggle') . '
                ' . $this->featureItem('Client dashboard flight price visibility') . '
                ' . $this->featureItem('Organizer dashboard flight price visibility') . '
                ' . $this->featureItem('Per-event flight price visibility settings') . '
            </div>
        </div>
        <div class="subsection">
            <div class="subsection-title">Flight Permissions</div>
            <div class="feature-list">
                ' . $this->featureItem('Flight sub-permissions for admins') . '
                ' . $this->featureItem('Resource-based flight permissions') . '
                ' . $this->featureItem('Flight export functionality (Excel)') . '
                ' . $this->featureItem('Global flights listing') . '
                ' . $this->featureItem('Per-event flights listing') . '
                ' . $this->featureItem('Single flight export') . '
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">6. Airport Management</div>
        <div class="subsection">
            <div class="subsection-title">Airport Operations</div>
            <div class="feature-list">
                ' . $this->featureItem('Airport creation and management') . '
                ' . $this->featureItem('Airport association with events') . '
                ' . $this->featureItem('Airport duplication functionality') . '
                ' . $this->featureItem('Airport API endpoints for frontend') . '
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">7. Wallet System</div>
        <div class="subsection">
            <div class="subsection-title">Wallet Operations</div>
            <div class="feature-list">
                ' . $this->featureItem('Automatic wallet creation on user registration') . '
                ' . $this->featureItem('Initial wallet balance (0.00)') . '
                ' . $this->featureItem('Wallet balance retrieval API') . '
                ' . $this->featureItem('French currency formatting (€)') . '
                ' . $this->featureItem('Automatic wallet credit on booking refund') . '
                ' . $this->featureItem('Wallet balance display in user dashboard') . '
                ' . $this->featureItem('Wallet payment deduction on booking') . '
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">8. Invoice System</div>
        <div class="subsection">
            <div class="subsection-title">Invoice Management</div>
            <div class="feature-list">
                ' . $this->featureItem('Automatic invoice generation on booking creation') . '
                ' . $this->featureItem('Invoice number generation') . '
                ' . $this->featureItem('Invoice PDF generation') . '
                ' . $this->featureItem('Invoice editing (admin only)') . '
                ' . $this->featureItem('Invoice status management (draft, sent, paid)') . '
                ' . $this->featureItem('Invoice email sending') . '
                ' . $this->featureItem('Invoice viewing and download') . '
                ' . $this->featureItem('Invoice regeneration functionality') . '
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">9. Voucher System</div>
        <div class="subsection">
            <div class="subsection-title">Voucher Operations</div>
            <div class="feature-list">
                ' . $this->featureItem('Automatic voucher generation on booking creation') . '
                ' . $this->featureItem('Voucher number generation (VOC-YYYYMMDDHHMMSS-XXXX format)') . '
                ' . $this->featureItem('Voucher PDF generation') . '
                ' . $this->featureItem('Voucher visibility (only when booking status is paid)') . '
                ' . $this->featureItem('Automatic voucher email on payment confirmation') . '
                ' . $this->featureItem('Voucher viewing API (authenticated users)') . '
                ' . $this->featureItem('Voucher download functionality') . '
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">10. Email Notification System</div>
        <div class="subsection">
            <div class="subsection-title">Email Features</div>
            <div class="feature-list">
                ' . $this->featureItem('Booking confirmation emails') . '
                ' . $this->featureItem('Booking notification emails (to admin)') . '
                ' . $this->featureItem('Invoice email sending') . '
                ' . $this->featureItem('Voucher email with PDF attachment') . '
                ' . $this->featureItem('Flight credentials email') . '
                ' . $this->featureItem('Newsletter subscription emails') . '
                ' . $this->featureItem('Email templates (Blade views)') . '
                ' . $this->featureItem('Queue support for email sending') . '
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">11. Newsletter System</div>
        <div class="subsection">
            <div class="subsection-title">Newsletter Operations</div>
            <div class="feature-list">
                ' . $this->featureItem('Newsletter subscription (public API)') . '
                ' . $this->featureItem('Newsletter unsubscribe functionality') . '
                ' . $this->featureItem('Newsletter subscriber management (admin)') . '
                ' . $this->featureItem('Newsletter email composition and sending') . '
                ' . $this->featureItem('Subscriber status management (active/inactive)') . '
                ' . $this->featureItem('Unsubscribe link generation') . '
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">12. Partner Management</div>
        <div class="subsection">
            <div class="subsection-title">Partner Operations</div>
            <div class="feature-list">
                ' . $this->featureItem('Partner creation and management') . '
                ' . $this->featureItem('Partner logo upload') . '
                ' . $this->featureItem('Partner active/inactive toggle') . '
                ' . $this->featureItem('Partner sort order management') . '
                ' . $this->featureItem('Partner duplication') . '
                ' . $this->featureItem('Partner API endpoints for frontend') . '
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">13. Admin Dashboard</div>
        <div class="subsection">
            <div class="subsection-title">Dashboard Features</div>
            <div class="feature-list">
                ' . $this->featureItem('Revenue statistics (daily)') . '
                ' . $this->featureItem('Booking count statistics') . '
                ' . $this->featureItem('Recent bookings display') . '
                ' . $this->featureItem('Admin action logging') . '
                ' . $this->featureItem('Admin log viewing (super-admin only)') . '
                ' . $this->featureItem('User management (super-admin only)') . '
                ' . $this->featureItem('Admin user management (super-admin only)') . '
                ' . $this->featureItem('Organizer dashboard') . '
                ' . $this->featureItem('Organizer bookings view') . '
                ' . $this->featureItem('Organizer flights view') . '
                ' . $this->featureItem('Organizer commissions view') . '
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">14. File Management</div>
        <div class="subsection">
            <div class="subsection-title">File Operations</div>
            <div class="feature-list">
                ' . $this->featureItem('Dual storage service (public and storage)') . '
                ' . $this->featureItem('Image upload handling') . '
                ' . $this->featureItem('PDF generation and storage') . '
                ' . $this->featureItem('File download functionality') . '
                ' . $this->featureItem('File deletion and cleanup') . '
                ' . $this->featureItem('Storage link management') . '
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">15. Export & Reporting</div>
        <div class="subsection">
            <div class="subsection-title">Export Features</div>
            <div class="feature-list">
                ' . $this->featureItem('Flight export to Excel (all flights)') . '
                ' . $this->featureItem('Flight export per event') . '
                ' . $this->featureItem('Single flight export') . '
                ' . $this->featureItem('Excel file generation with Maatwebsite Excel') . '
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">16. Security Features</div>
        <div class="subsection">
            <div class="subsection-title">Security Implementations</div>
            <div class="feature-list">
                ' . $this->featureItem('OWASP security best practices') . '
                ' . $this->featureItem('Rate limiting on API endpoints') . '
                ' . $this->featureItem('CORS configuration') . '
                ' . $this->featureItem('Content Security Policy (CSP) support') . '
                ' . $this->featureItem('SQL injection prevention (Eloquent ORM)') . '
                ' . $this->featureItem('XSS protection') . '
                ' . $this->featureItem('CSRF protection') . '
                ' . $this->featureItem('Input sanitization service') . '
                ' . $this->featureItem('Password strength requirements') . '
                ' . $this->featureItem('Token security (max 5 per user)') . '
                ' . $this->featureItem('Sensitive data hiding (password, tokens)') . '
                ' . $this->featureItem('Role-based route protection') . '
                ' . $this->featureItem('Resource permission checking') . '
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">17. API Features</div>
        <div class="subsection">
            <div class="subsection-title">REST API Endpoints</div>
            <div class="feature-list">
                ' . $this->featureItem('Public event listing API') . '
                ' . $this->featureItem('Event details API') . '
                ' . $this->featureItem('Hotel listing API (by event)') . '
                ' . $this->featureItem('Hotel details API') . '
                ' . $this->featureItem('Flight listing API (by event)') . '
                ' . $this->featureItem('Flight details API') . '
                ' . $this->featureItem('Airport listing API') . '
                ' . $this->featureItem('Partner listing API') . '
                ' . $this->featureItem('Event content API (conditions, info, FAQ)') . '
                ' . $this->featureItem('Authenticated booking API') . '
                ' . $this->featureItem('User wallet API') . '
                ' . $this->featureItem('Voucher API (paid bookings only)') . '
                ' . $this->featureItem('Newsletter subscription API') . '
                ' . $this->featureItem('Maintenance mode API') . '
                ' . $this->featureItem('Standardized API response format') . '
                ' . $this->featureItem('Error handling and validation') . '
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">18. Maintenance & System</div>
        <div class="subsection">
            <div class="subsection-title">System Features</div>
            <div class="feature-list">
                ' . $this->featureItem('Maintenance mode toggle') . '
                ' . $this->featureItem('Cache clearing functionality') . '
                ' . $this->featureItem('Database migrations (88 migration files)') . '
                ' . $this->featureItem('Database seeders') . '
                ' . $this->featureItem('Artisan commands for admin creation') . '
                ' . $this->featureItem('Localization support (French/English)') . '
                ' . $this->featureItem('Multi-language support') . '
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">19. User Interface</div>
        <div class="subsection">
            <div class="subsection-title">Admin Dashboard UI</div>
            <div class="feature-list">
                ' . $this->featureItem('Blade template-based admin interface') . '
                ' . $this->featureItem('TailwindCSS styling') . '
                ' . $this->featureItem('Shadcn UI components') . '
                ' . $this->featureItem('Responsive design') . '
                ' . $this->featureItem('Livewire v3 for real-time updates') . '
                ' . $this->featureItem('Alpine.js for interactivity') . '
                ' . $this->featureItem('Lucide icons integration') . '
                ' . $this->featureItem('Modal dialogs') . '
                ' . $this->featureItem('Data tables with pagination') . '
                ' . $this->featureItem('Search and filter functionality') . '
                ' . $this->featureItem('Expandable detail rows') . '
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">20. Database & Models</div>
        <div class="subsection">
            <div class="subsection-title">Database Structure</div>
            <div class="feature-list">
                ' . $this->featureItem('23 model files') . '
                ' . $this->featureItem('88 migration files') . '
                ' . $this->featureItem('Eloquent relationships (hasMany, belongsTo, hasOne)') . '
                ' . $this->featureItem('Model events and observers') . '
                ' . $this->featureItem('Soft deletes support') . '
                ' . $this->featureItem('Timestamps tracking') . '
                ' . $this->featureItem('JSON column support') . '
            </div>
        </div>
    </div>

    <div style="page-break-before: always; margin-top: 40px; border-top: 3px solid #00adf1; padding-top: 20px;">
        <h2 style="color: #00adf1; text-align: center; font-size: 22pt; margin-bottom: 30px;">FRONTEND FEATURES</h2>
    </div>

    <div class="section">
        <div class="section-title">21. Frontend Authentication & User Management</div>
        <div class="subsection">
            <div class="subsection-title">Authentication System</div>
            <div class="feature-list">
                ' . $this->featureItem('React Router DOM for client-side routing') . '
                ' . $this->featureItem('Laravel Sanctum token-based authentication for API requests') . '
                ' . $this->featureItem('User registration with form validation') . '
                ' . $this->featureItem('User login with token storage (localStorage)') . '
                ' . $this->featureItem('Password validation (min 8 chars, uppercase, lowercase, number)') . '
                ' . $this->featureItem('Token management (automatic token injection in API requests)') . '
                ' . $this->featureItem('User logout functionality with token revocation') . '
                ' . $this->featureItem('User profile update page') . '
                ' . $this->featureItem('Password change functionality') . '
                ' . $this->featureItem('Protected routes (authentication required)') . '
                ' . $this->featureItem('Automatic token refresh handling') . '
                ' . $this->featureItem('Token expiration detection') . '
            </div>
        </div>
        <div class="subsection">
            <div class="subsection-title">User Session Management</div>
            <div class="feature-list">
                ' . $this->featureItem('Token persistence in localStorage') . '
                ' . $this->featureItem('Automatic token injection in API headers') . '
                ' . $this->featureItem('Session state management with React hooks') . '
                ' . $this->featureItem('User data caching and refresh') . '
                ' . $this->featureItem('Logout with backend token revocation') . '
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">22. Event & Accommodation Display</div>
        <div class="subsection">
            <div class="subsection-title">Event Listing & Navigation</div>
            <div class="feature-list">
                ' . $this->featureItem('Homepage with events carousel/grid display') . '
                ' . $this->featureItem('Event card components with images and details') . '
                ' . $this->featureItem('Event search functionality') . '
                ' . $this->featureItem('Event filtering and sorting') . '
                ' . $this->featureItem('Dynamic event routing (/:eventSlug)') . '
                ' . $this->featureItem('Event landing page with hero section') . '
                ' . $this->featureItem('Event navigation menu (Conditions, FAQ, Info)') . '
                ' . $this->featureItem('Event content pages (Conditions, FAQ, General Info)') . '
                ' . $this->featureItem('Event breadcrumbs navigation') . '
                ' . $this->featureItem('Event logo and banner display') . '
            </div>
        </div>
        <div class="subsection">
            <div class="subsection-title">Event Content Management</div>
            <div class="feature-list">
                ' . $this->featureItem('Rich text content rendering (sanitized HTML)') . '
                ' . $this->featureItem('Multi-language content support') . '
                ' . $this->featureItem('Event-specific content pages') . '
                ' . $this->featureItem('Safe HTML rendering component (XSS protection)') . '
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">23. Hotel Display & Booking</div>
        <div class="subsection">
            <div class="subsection-title">Hotel Browsing</div>
            <div class="feature-list">
                ' . $this->featureItem('Hotel listing page (/:eventSlug/hotels)') . '
                ' . $this->featureItem('Hotel card components with images') . '
                ' . $this->featureItem('Hotel carousel display') . '
                ' . $this->featureItem('Hotel grid/list view') . '
                ' . $this->featureItem('Hotel details page (/:eventSlug/hotels/:hotelId)') . '
                ' . $this->featureItem('Hotel image galleries with carousel') . '
                ' . $this->featureItem('Hotel packages display') . '
                ' . $this->featureItem('Hotel pricing information (HT/TTC)') . '
                ' . $this->featureItem('Hotel availability display') . '
                ' . $this->featureItem('Hotel location and description') . '
            </div>
        </div>
        <div class="subsection">
            <div class="subsection-title">Hotel Booking System</div>
            <div class="feature-list">
                ' . $this->featureItem('Booking form component with validation') . '
                ' . $this->featureItem('Guest information capture (name, email, phone, company)') . '
                ' . $this->featureItem('Check-in and check-out date selection') . '
                ' . $this->featureItem('Guests count selection') . '
                ' . $this->featureItem('Resident names input (up to 2 residents)') . '
                ' . $this->featureItem('Special instructions/requests field') . '
                ' . $this->featureItem('Flight information input (optional)') . '
                ' . $this->featureItem('Booking reference linking (link to existing flight booking)') . '
                ' . $this->featureItem('Booking confirmation page') . '
                ' . $this->featureItem('Booking status display') . '
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">24. Flight Display & Management</div>
        <div class="subsection">
            <div class="subsection-title">Flight Browsing</div>
            <div class="feature-list">
                ' . $this->featureItem('Flight listing on event landing page') . '
                ' . $this->featureItem('Flight carousel component (FlightsSection)') . '
                ' . $this->featureItem('Flight card components with details') . '
                ' . $this->featureItem('Flight details page (/:eventSlug/flights/:flightId)') . '
                ' . $this->featureItem('Flight class display (economy, business, first)') . '
                ' . $this->featureItem('Flight category display (one-way, round-trip)') . '
                ' . $this->featureItem('Departure flight details (date, time, number, airports)') . '
                ' . $this->featureItem('Return flight details (for round-trip)') . '
                ' . $this->featureItem('eTicket link display') . '
                ' . $this->featureItem('Flight reference display') . '
            </div>
        </div>
        <div class="subsection">
            <div class="subsection-title">Flight Price Visibility</div>
            <div class="feature-list">
                ' . $this->featureItem('Public price visibility toggle support') . '
                ' . $this->featureItem('Conditional price display based on event settings') . '
                ' . $this->featureItem('"Price on request" message when prices hidden') . '
                ' . $this->featureItem('Price formatting (French currency: €)') . '
                ' . $this->featureItem('Price visibility handling in carousel and details pages') . '
            </div>
        </div>
        <div class="subsection">
            <div class="subsection-title">Flight Booking Integration</div>
            <div class="feature-list">
                ' . $this->featureItem('Flight booking reference field in hotel booking form') . '
                ' . $this->featureItem('Automatic flight details pre-filling from reference') . '
                ' . $this->featureItem('Flight details optional when reference provided') . '
                ' . $this->featureItem('Flight number validation') . '
                ' . $this->featureItem('Flight booking linking functionality') . '
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">25. Frontend Booking System</div>
        <div class="subsection">
            <div class="subsection-title">Booking Management</div>
            <div class="feature-list">
                ' . $this->featureItem('Booking creation via API (authenticated users)') . '
                ' . $this->featureItem('Booking form with comprehensive validation') . '
                ' . $this->featureItem('Automatic booking reference display') . '
                ' . $this->featureItem('Booking status tracking (pending, confirmed, paid, cancelled, refunded)') . '
                ' . $this->featureItem('Guest information management') . '
                ' . $this->featureItem('Date selection with validation') . '
                ' . $this->featureItem('Guests count management') . '
                ' . $this->featureItem('Resident names management') . '
                ' . $this->featureItem('Special instructions field') . '
                ' . $this->featureItem('Flight information integration') . '
            </div>
        </div>
        <div class="subsection">
            <div class="subsection-title">Booking Form Features</div>
            <div class="feature-list">
                ' . $this->featureItem('Real-time form validation') . '
                ' . $this->featureItem('Input sanitization (OWASP compliant)') . '
                ' . $this->featureItem('Required field indicators') . '
                ' . $this->featureItem('Error message display') . '
                ' . $this->featureItem('Success confirmation') . '
                ' . $this->featureItem('Terms and conditions acceptance') . '
                ' . $this->featureItem('Booking reference lookup') . '
                ' . $this->featureItem('Flight details conditional requirement') . '
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">26. User Dashboard</div>
        <div class="subsection">
            <div class="subsection-title">Dashboard Features</div>
            <div class="feature-list">
                ' . $this->featureItem('User profile display') . '
                ' . $this->featureItem('Wallet balance display (French currency formatting)') . '
                ' . $this->featureItem('Booking history listing') . '
                ' . $this->featureItem('Booking status display') . '
                ' . $this->featureItem('Booking details view') . '
                ' . $this->featureItem('Voucher listing (paid bookings only)') . '
                ' . $this->featureItem('Voucher download functionality') . '
                ' . $this->featureItem('Document upload sections') . '
            </div>
        </div>
        <div class="subsection">
            <div class="subsection-title">Document Upload System</div>
            <div class="feature-list">
                ' . $this->featureItem('Payment document upload (Ordre de paiement)') . '
                ' . $this->featureItem('Flight ticket upload') . '
                ' . $this->featureItem('Image and PDF support') . '
                ' . $this->featureItem('File validation (type, size)') . '
                ' . $this->featureItem('Upload progress indication') . '
                ' . $this->featureItem('Success/error feedback') . '
                ' . $this->featureItem('Per-booking upload support') . '
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">27. Admin Impersonation System</div>
        <div class="subsection">
            <div class="subsection-title">Impersonation Features</div>
            <div class="feature-list">
                ' . $this->featureItem('Token detection from URL query parameter') . '
                ' . $this->featureItem('Automatic authentication with impersonation token') . '
                ' . $this->featureItem('Impersonation banner display (yellow warning banner)') . '
                ' . $this->featureItem('Impersonated user name display') . '
                ' . $this->featureItem('Stop impersonation functionality') . '
                ' . $this->featureItem('Token removal from URL (security measure)') . '
                ' . $this->featureItem('Backend redirect on stop impersonation') . '
                ' . $this->featureItem('Impersonation status tracking') . '
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">28. Frontend Security Features (OWASP Compliance)</div>
        <div class="subsection">
            <div class="subsection-title">Input Sanitization</div>
            <div class="feature-list">
                ' . $this->featureItem('String sanitization (HTML entities, null bytes, length limits)') . '
                ' . $this->featureItem('Email sanitization (format validation, dangerous characters)') . '
                ' . $this->featureItem('Phone sanitization (non-digit removal, length limits)') . '
                ' . $this->featureItem('URL sanitization (dangerous characters, length limits)') . '
                ' . $this->featureItem('Number sanitization (non-numeric removal, range validation)') . '
                ' . $this->featureItem('Date sanitization (format validation, real date verification)') . '
                ' . $this->featureItem('HTML sanitization (DOMPurify integration)') . '
                ' . $this->featureItem('Form data sanitization (bulk sanitization with schema)') . '
            </div>
        </div>
        <div class="subsection">
            <div class="subsection-title">Input Validation</div>
            <div class="feature-list">
                ' . $this->featureItem('Email validation (format, domain)') . '
                ' . $this->featureItem('Phone validation (format, length)') . '
                ' . $this->featureItem('Password validation (strength requirements)') . '
                ' . $this->featureItem('Date validation (format, range)') . '
                ' . $this->featureItem('File validation (type, size, extension)') . '
                ' . $this->featureItem('Form validation (client-side, real-time)') . '
            </div>
        </div>
        <div class="subsection">
            <div class="subsection-title">XSS Prevention</div>
            <div class="feature-list">
                ' . $this->featureItem('SafeHTML component (replaces dangerouslySetInnerHTML)') . '
                ' . $this->featureItem('DOMPurify integration for HTML sanitization') . '
                ' . $this->featureItem('Content Security Policy support') . '
                ' . $this->featureItem('Automatic HTML escaping in forms') . '
            </div>
        </div>
        <div class="subsection">
            <div class="subsection-title">CSRF Protection</div>
            <div class="feature-list">
                ' . $this->featureItem('CSRFInitializer component for token management') . '
                ' . $this->featureItem('Automatic CSRF cookie fetching') . '
                ' . $this->featureItem('CSRF token injection in requests') . '
                ' . $this->featureItem('Sanctum CSRF integration') . '
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">29. Internationalization (i18n)</div>
        <div class="subsection">
            <div class="subsection-title">Multi-Language Support</div>
            <div class="feature-list">
                ' . $this->featureItem('French (fr) as default language') . '
                ' . $this->featureItem('English (en) language support') . '
                ' . $this->featureItem('Language switcher component in navbar') . '
                ' . $this->featureItem('Dynamic content translation') . '
                ' . $this->featureItem('Translation files (messages/fr.json, messages/en.json)') . '
                ' . $this->featureItem('useI18n hook for translations') . '
                ' . $this->featureItem('Locale persistence in localStorage') . '
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">30. UI/UX Components</div>
        <div class="subsection">
            <div class="subsection-title">Layout Components</div>
            <div class="feature-list">
                ' . $this->featureItem('Navbar with responsive design') . '
                ' . $this->featureItem('Footer with links and information') . '
                ' . $this->featureItem('MainLayout wrapper component') . '
                ' . $this->featureItem('EventNavbar for event-specific navigation') . '
                ' . $this->featureItem('Breadcrumbs navigation component') . '
                ' . $this->featureItem('WhatsApp button (floating action button)') . '
            </div>
        </div>
        <div class="subsection">
            <div class="subsection-title">Form Components</div>
            <div class="feature-list">
                ' . $this->featureItem('Input component with validation') . '
                ' . $this->featureItem('SearchField component with icons') . '
                ' . $this->featureItem('DatePicker component') . '
                ' . $this->featureItem('GuestsDropdown component') . '
                ' . $this->featureItem('BookingForm component (comprehensive)') . '
            </div>
        </div>
        <div class="subsection">
            <div class="subsection-title">Display Components</div>
            <div class="feature-list">
                ' . $this->featureItem('Card components (Event, Hotel, Flight, Package)') . '
                ' . $this->featureItem('Carousel components (Events, Hotels, Flights)') . '
                ' . $this->featureItem('ImageCarousel for galleries') . '
                ' . $this->featureItem('Badge component for status') . '
                ' . $this->featureItem('Spinner loading component') . '
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">31. API Integration</div>
        <div class="subsection">
            <div class="subsection-title">API Client</div>
            <div class="feature-list">
                ' . $this->featureItem('Centralized API client (lib/api.js)') . '
                ' . $this->featureItem('Axios-based HTTP client') . '
                ' . $this->featureItem('Automatic token injection') . '
                ' . $this->featureItem('CSRF cookie handling') . '
                ' . $this->featureItem('Error handling and retry logic') . '
                ' . $this->featureItem('Request/response interceptors') . '
            </div>
        </div>
        <div class="subsection">
            <div class="subsection-title">API Endpoints Integration</div>
            <div class="feature-list">
                ' . $this->featureItem('Public event listing API') . '
                ' . $this->featureItem('Event details API') . '
                ' . $this->featureItem('Hotel listing API (by event)') . '
                ' . $this->featureItem('Flight listing API (by event)') . '
                ' . $this->featureItem('Authenticated booking API') . '
                ' . $this->featureItem('User wallet API') . '
                ' . $this->featureItem('Voucher API (paid bookings only)') . '
                ' . $this->featureItem('Document upload API') . '
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">32. Search & Filtering</div>
        <div class="subsection">
            <div class="subsection-title">Search Functionality</div>
            <div class="feature-list">
                ' . $this->featureItem('Event search on homepage') . '
                ' . $this->featureItem('Hotel search by event') . '
                ' . $this->featureItem('Date range search (check-in, check-out)') . '
                ' . $this->featureItem('Guests count selection') . '
                ' . $this->featureItem('Search form with validation') . '
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">33. Responsive Design</div>
        <div class="subsection">
            <div class="subsection-title">Device Support</div>
            <div class="feature-list">
                ' . $this->featureItem('Responsive layouts for all pages') . '
                ' . $this->featureItem('Mobile menu navigation') . '
                ' . $this->featureItem('Touch-friendly components') . '
                ' . $this->featureItem('Mobile-optimized forms') . '
                ' . $this->featureItem('Responsive carousels') . '
                ' . $this->featureItem('Tablet-optimized layouts') . '
                ' . $this->featureItem('Desktop-optimized layouts') . '
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">34. Performance Optimization</div>
        <div class="subsection">
            <div class="subsection-title">Optimization Features</div>
            <div class="feature-list">
                ' . $this->featureItem('Code splitting with React Router') . '
                ' . $this->featureItem('Bundle size optimization') . '
                ' . $this->featureItem('Tree shaking (Vite)') . '
                ' . $this->featureItem('Spinner components for loading') . '
                ' . $this->featureItem('Progressive loading') . '
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">35. Frontend Technology Stack</div>
        <div class="subsection">
            <div class="subsection-title">Frontend Framework</div>
            <div class="feature-list">
                ' . $this->featureItem('React 18.2+') . '
                ' . $this->featureItem('React Router DOM 6.20+') . '
                ' . $this->featureItem('Vite 5.0+') . '
                ' . $this->featureItem('Axios (Latest)') . '
                ' . $this->featureItem('DOMPurify (Latest)') . '
                ' . $this->featureItem('React Helmet Async (Latest)') . '
                ' . $this->featureItem('Embla Carousel (Latest)') . '
                ' . $this->featureItem('TailwindCSS for styling') . '
            </div>
        </div>
    </div>';
    }

    private function getFrenchSections(): string
    {
        return '
    <div class="section">
        <div class="section-title">1. Authentification et Gestion des Utilisateurs</div>
        <div class="subsection">
            <div class="subsection-title">Système d\'Authentification</div>
            <div class="feature-list">
                ' . $this->featureItem('Authentification par token Laravel Sanctum pour l\'API') . '
                ' . $this->featureItem('Authentification par session Laravel Breeze pour le tableau de bord admin') . '
                ' . $this->featureItem('Inscription utilisateur avec création automatique de portefeuille') . '
                ' . $this->featureItem('Connexion utilisateur avec génération de token') . '
                ' . $this->featureItem('Validation du mot de passe (min 8 caractères, majuscule, minuscule, chiffre)') . '
                ' . $this->featureItem('Hachage du mot de passe avec bcrypt') . '
                ' . $this->featureItem('Gestion des tokens (max 5 tokens par utilisateur, nettoyage automatique)') . '
                ' . $this->featureItem('Fonctionnalité de déconnexion') . '
                ' . $this->featureItem('Mise à jour du profil utilisateur') . '
                ' . $this->featureItem('Fonctionnalité de changement de mot de passe') . '
            </div>
        </div>
        <div class="subsection">
            <div class="subsection-title">Contrôle d\'Accès Basé sur les Rôles (RBAC)</div>
            <div class="feature-list">
                ' . $this->featureItem('Rôle Super Admin avec accès complet au système') . '
                ' . $this->featureItem('Rôle Admin avec permissions configurables') . '
                ' . $this->featureItem('Rôle Organisateur pour la gestion d\'événements') . '
                ' . $this->featureItem('Rôle utilisateur régulier pour les réservations') . '
                ' . $this->featureItem('Système de permissions basé sur les ressources') . '
                ' . $this->featureItem('Middleware de vérification des permissions') . '
                ' . $this->featureItem('Fonctionnalité d\'usurpation d\'identité admin (super-admin uniquement)') . '
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">2. Gestion des Événements et Hébergements</div>
        <div class="subsection">
            <div class="subsection-title">Gestion des Événements</div>
            <div class="feature-list">
                ' . $this->featureItem('Créer, lire, mettre à jour, supprimer des événements (hébergements)') . '
                ' . $this->featureItem('Génération et gestion du slug d\'événement') . '
                ' . $this->featureItem('Téléchargement d\'images de logo et bannière d\'événement') . '
                ' . $this->featureItem('Description et détails de l\'événement') . '
                ' . $this->featureItem('Gestion du statut de l\'événement (actif/inactif)') . '
                ' . $this->featureItem('Fonctionnalité de duplication d\'événement') . '
                ' . $this->featureItem('Attribution d\'organisateur aux événements') . '
                ' . $this->featureItem('Configuration du pourcentage de commission par événement') . '
                ' . $this->featureItem('Configuration des liens de menu d\'événement (JSON)') . '
            </div>
        </div>
        <div class="subsection">
            <div class="subsection-title">Gestion du Contenu des Événements</div>
            <div class="feature-list">
                ' . $this->featureItem('Pages de contenu personnalisées (Conditions, Info, FAQ)') . '
                ' . $this->featureItem('Édition de contenu en texte enrichi') . '
                ' . $this->featureItem('Gestion du contenu par événement/type') . '
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">3. Gestion des Hôtels</div>
        <div class="subsection">
            <div class="subsection-title">Opérations Hôtelières</div>
            <div class="feature-list">
                ' . $this->featureItem('Créer, lire, mettre à jour, supprimer des hôtels') . '
                ' . $this->featureItem('Génération du slug d\'hôtel') . '
                ' . $this->featureItem('Localisation et description de l\'hôtel') . '
                ' . $this->featureItem('Gestion du statut de l\'hôtel') . '
                ' . $this->featureItem('Fonctionnalité de duplication d\'hôtel') . '
                ' . $this->featureItem('Association d\'hôtel avec des événements') . '
            </div>
        </div>
        <div class="subsection">
            <div class="subsection-title">Gestion des Images d\'Hôtel</div>
            <div class="feature-list">
                ' . $this->featureItem('Téléchargements multiples d\'images par hôtel') . '
                ' . $this->featureItem('Réorganisation des images (glisser-déposer)') . '
                ' . $this->featureItem('Mise à jour et suppression d\'images') . '
                ' . $this->featureItem('Gestionnaire d\'images alimenté par Livewire') . '
                ' . $this->featureItem('Stockage d\'images avec service de stockage double') . '
            </div>
        </div>
        <div class="subsection">
            <div class="subsection-title">Forfaits Hôteliers</div>
            <div class="feature-list">
                ' . $this->featureItem('Création et gestion de forfaits') . '
                ' . $this->featureItem('Tarification des forfaits (HT et TTC)') . '
                ' . $this->featureItem('Configuration du type de chambre') . '
                ' . $this->featureItem('Gestion de la disponibilité des forfaits') . '
                ' . $this->featureItem('Duplication de forfait') . '
                ' . $this->featureItem('Association de forfait avec les hôtels') . '
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">4. Système de Réservation</div>
        <div class="subsection">
            <div class="subsection-title">Gestion des Réservations</div>
            <div class="feature-list">
                ' . $this->featureItem('Créer des réservations via API (utilisateurs authentifiés)') . '
                ' . $this->featureItem('Génération automatique de référence de réservation') . '
                ' . $this->featureItem('Gestion du statut de réservation (en attente, confirmée, payée, annulée, remboursée)') . '
                ' . $this->featureItem('Capture d\'informations client (nom, email, téléphone, entreprise)') . '
                ' . $this->featureItem('Gestion des dates d\'arrivée et de départ') . '
                ' . $this->featureItem('Suivi du nombre d\'invités') . '
                ' . $this->featureItem('Noms des résidents (jusqu\'à 2 résidents)') . '
                ' . $this->featureItem('Champ d\'instructions/requêtes spéciales') . '
                ' . $this->featureItem('Informations de vol dans les réservations') . '
                ' . $this->featureItem('Recherche et filtrage de réservations') . '
                ' . $this->featureItem('Vue des détails de réservation avec lignes extensibles') . '
            </div>
        </div>
        <div class="subsection">
            <div class="subsection-title">Système de Paiement</div>
            <div class="feature-list">
                ' . $this->featureItem('Sélection de méthode de paiement (portefeuille, virement bancaire, les deux)') . '
                ' . $this->featureItem('Intégration du paiement par portefeuille') . '
                ' . $this->featureItem('Support du paiement par virement bancaire') . '
                ' . $this->featureItem('Paiement mixte (portefeuille + banque)') . '
                ' . $this->featureItem('Téléchargement de document de paiement') . '
                ' . $this->featureItem('Téléchargement de billet d\'avion') . '
                ' . $this->featureItem('Confirmation automatique de réservation pour les paiements par portefeuille') . '
                ' . $this->featureItem('Suivi du montant de paiement (wallet_amount, bank_amount)') . '
            </div>
        </div>
        <div class="subsection">
            <div class="subsection-title">Système de Remboursement</div>
            <div class="feature-list">
                ' . $this->featureItem('Traitement du remboursement de réservation') . '
                ' . $this->featureItem('Crédit automatique du portefeuille lors du remboursement') . '
                ' . $this->featureItem('Suivi du montant de remboursement') . '
                ' . $this->featureItem('Notes/remarques de remboursement') . '
                ' . $this->featureItem('Restauration automatique de la disponibilité de la chambre lors du remboursement') . '
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">5. Système de Gestion des Vols</div>
        <div class="subsection">
            <div class="subsection-title">Gestion des Réservations de Vol</div>
            <div class="feature-list">
                ' . $this->featureItem('Création et gestion de vols') . '
                ' . $this->featureItem('Génération automatique de référence de vol') . '
                ' . $this->featureItem('Sélection de classe de vol (économique, affaires, première)') . '
                ' . $this->featureItem('Catégorie de vol (aller simple, aller-retour)') . '
                ' . $this->featureItem('Détails du vol de départ (date, heure, numéro, aéroports, prix)') . '
                ' . $this->featureItem('Détails du vol de retour (pour aller-retour)') . '
                ' . $this->featureItem('Téléchargement et gestion d\'eTicket') . '
                ' . $this->featureItem('Suivi du numéro et de la référence d\'eTicket') . '
                ' . $this->featureItem('Type de bénéficiaire (organisateur ou client)') . '
                ' . $this->featureItem('Création automatique de compte client pour les réservations de vol') . '
                ' . $this->featureItem('Génération de PDF des identifiants de vol') . '
                ' . $this->featureItem('Envoi d\'email des identifiants de vol') . '
                ' . $this->featureItem('Gestion du statut de vol (en attente, payé)') . '
                ' . $this->featureItem('Suivi de la méthode de paiement de vol') . '
            </div>
        </div>
        <div class="subsection">
            <div class="subsection-title">Visibilité des Prix des Vols</div>
            <div class="feature-list">
                ' . $this->featureItem('Bascule de visibilité des prix de vol publics') . '
                ' . $this->featureItem('Visibilité des prix de vol dans le tableau de bord client') . '
                ' . $this->featureItem('Visibilité des prix de vol dans le tableau de bord organisateur') . '
                ' . $this->featureItem('Paramètres de visibilité des prix de vol par événement') . '
            </div>
        </div>
        <div class="subsection">
            <div class="subsection-title">Permissions de Vol</div>
            <div class="feature-list">
                ' . $this->featureItem('Sous-permissions de vol pour les admins') . '
                ' . $this->featureItem('Permissions de vol basées sur les ressources') . '
                ' . $this->featureItem('Fonctionnalité d\'export de vol (Excel)') . '
                ' . $this->featureItem('Liste globale des vols') . '
                ' . $this->featureItem('Liste des vols par événement') . '
                ' . $this->featureItem('Export de vol unique') . '
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">6. Gestion des Aéroports</div>
        <div class="subsection">
            <div class="subsection-title">Opérations Aéroportuaires</div>
            <div class="feature-list">
                ' . $this->featureItem('Création et gestion d\'aéroports') . '
                ' . $this->featureItem('Association d\'aéroport avec des événements') . '
                ' . $this->featureItem('Fonctionnalité de duplication d\'aéroport') . '
                ' . $this->featureItem('Points de terminaison API d\'aéroport pour le frontend') . '
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">7. Système de Portefeuille</div>
        <div class="subsection">
            <div class="subsection-title">Opérations de Portefeuille</div>
            <div class="feature-list">
                ' . $this->featureItem('Création automatique de portefeuille lors de l\'inscription utilisateur') . '
                ' . $this->featureItem('Solde initial du portefeuille (0,00)') . '
                ' . $this->featureItem('API de récupération du solde du portefeuille') . '
                ' . $this->featureItem('Formatage de devise française (€)') . '
                ' . $this->featureItem('Crédit automatique du portefeuille lors du remboursement de réservation') . '
                ' . $this->featureItem('Affichage du solde du portefeuille dans le tableau de bord utilisateur') . '
                ' . $this->featureItem('Déduction du paiement par portefeuille lors de la réservation') . '
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">8. Système de Facturation</div>
        <div class="subsection">
            <div class="subsection-title">Gestion des Factures</div>
            <div class="feature-list">
                ' . $this->featureItem('Génération automatique de facture lors de la création de réservation') . '
                ' . $this->featureItem('Génération du numéro de facture') . '
                ' . $this->featureItem('Génération de PDF de facture') . '
                ' . $this->featureItem('Édition de facture (admin uniquement)') . '
                ' . $this->featureItem('Gestion du statut de facture (brouillon, envoyée, payée)') . '
                ' . $this->featureItem('Envoi d\'email de facture') . '
                ' . $this->featureItem('Visualisation et téléchargement de facture') . '
                ' . $this->featureItem('Fonctionnalité de régénération de facture') . '
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">9. Système de Vouchers</div>
        <div class="subsection">
            <div class="subsection-title">Opérations de Voucher</div>
            <div class="feature-list">
                ' . $this->featureItem('Génération automatique de voucher lors de la création de réservation') . '
                ' . $this->featureItem('Génération du numéro de voucher (format VOC-YYYYMMDDHHMMSS-XXXX)') . '
                ' . $this->featureItem('Génération de PDF de voucher') . '
                ' . $this->featureItem('Visibilité du voucher (uniquement lorsque le statut de réservation est payé)') . '
                ' . $this->featureItem('Email automatique de voucher lors de la confirmation de paiement') . '
                ' . $this->featureItem('API de visualisation de voucher (utilisateurs authentifiés)') . '
                ' . $this->featureItem('Fonctionnalité de téléchargement de voucher') . '
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">10. Système de Notification par Email</div>
        <div class="subsection">
            <div class="subsection-title">Fonctionnalités Email</div>
            <div class="feature-list">
                ' . $this->featureItem('Emails de confirmation de réservation') . '
                ' . $this->featureItem('Emails de notification de réservation (à l\'admin)') . '
                ' . $this->featureItem('Envoi d\'email de facture') . '
                ' . $this->featureItem('Email de voucher avec pièce jointe PDF') . '
                ' . $this->featureItem('Email des identifiants de vol') . '
                ' . $this->featureItem('Emails d\'abonnement à la newsletter') . '
                ' . $this->featureItem('Modèles d\'email (vues Blade)') . '
                ' . $this->featureItem('Support de file d\'attente pour l\'envoi d\'emails') . '
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">11. Système de Newsletter</div>
        <div class="subsection">
            <div class="subsection-title">Opérations de Newsletter</div>
            <div class="feature-list">
                ' . $this->featureItem('Abonnement à la newsletter (API publique)') . '
                ' . $this->featureItem('Fonctionnalité de désabonnement à la newsletter') . '
                ' . $this->featureItem('Gestion des abonnés à la newsletter (admin)') . '
                ' . $this->featureItem('Composition et envoi d\'email de newsletter') . '
                ' . $this->featureItem('Gestion du statut des abonnés (actif/inactif)') . '
                ' . $this->featureItem('Génération de lien de désabonnement') . '
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">12. Gestion des Partenaires</div>
        <div class="subsection">
            <div class="subsection-title">Opérations de Partenaires</div>
            <div class="feature-list">
                ' . $this->featureItem('Création et gestion de partenaires') . '
                ' . $this->featureItem('Téléchargement de logo de partenaire') . '
                ' . $this->featureItem('Bascule actif/inactif de partenaire') . '
                ' . $this->featureItem('Gestion de l\'ordre de tri des partenaires') . '
                ' . $this->featureItem('Duplication de partenaire') . '
                ' . $this->featureItem('Points de terminaison API de partenaire pour le frontend') . '
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">13. Tableau de Bord Admin</div>
        <div class="subsection">
            <div class="subsection-title">Fonctionnalités du Tableau de Bord</div>
            <div class="feature-list">
                ' . $this->featureItem('Statistiques de revenus (quotidiennes)') . '
                ' . $this->featureItem('Statistiques du nombre de réservations') . '
                ' . $this->featureItem('Affichage des réservations récentes') . '
                ' . $this->featureItem('Journalisation des actions admin') . '
                ' . $this->featureItem('Visualisation du journal admin (super-admin uniquement)') . '
                ' . $this->featureItem('Gestion des utilisateurs (super-admin uniquement)') . '
                ' . $this->featureItem('Gestion des utilisateurs admin (super-admin uniquement)') . '
                ' . $this->featureItem('Tableau de bord organisateur') . '
                ' . $this->featureItem('Vue des réservations organisateur') . '
                ' . $this->featureItem('Vue des vols organisateur') . '
                ' . $this->featureItem('Vue des commissions organisateur') . '
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">14. Gestion des Fichiers</div>
        <div class="subsection">
            <div class="subsection-title">Opérations de Fichiers</div>
            <div class="feature-list">
                ' . $this->featureItem('Service de stockage double (public et storage)') . '
                ' . $this->featureItem('Gestion du téléchargement d\'images') . '
                ' . $this->featureItem('Génération et stockage de PDF') . '
                ' . $this->featureItem('Fonctionnalité de téléchargement de fichiers') . '
                ' . $this->featureItem('Suppression et nettoyage de fichiers') . '
                ' . $this->featureItem('Gestion des liens de stockage') . '
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">15. Export et Rapports</div>
        <div class="subsection">
            <div class="subsection-title">Fonctionnalités d\'Export</div>
            <div class="feature-list">
                ' . $this->featureItem('Export de vol vers Excel (tous les vols)') . '
                ' . $this->featureItem('Export de vol par événement') . '
                ' . $this->featureItem('Export de vol unique') . '
                ' . $this->featureItem('Génération de fichier Excel avec Maatwebsite Excel') . '
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">16. Fonctionnalités de Sécurité</div>
        <div class="subsection">
            <div class="subsection-title">Implémentations de Sécurité</div>
            <div class="feature-list">
                ' . $this->featureItem('Meilleures pratiques de sécurité OWASP') . '
                ' . $this->featureItem('Limitation de débit sur les points de terminaison API') . '
                ' . $this->featureItem('Configuration CORS') . '
                ' . $this->featureItem('Support de Content Security Policy (CSP)') . '
                ' . $this->featureItem('Prévention de l\'injection SQL (Eloquent ORM)') . '
                ' . $this->featureItem('Protection XSS') . '
                ' . $this->featureItem('Protection CSRF') . '
                ' . $this->featureItem('Service de nettoyage des entrées') . '
                ' . $this->featureItem('Exigences de force du mot de passe') . '
                ' . $this->featureItem('Sécurité des tokens (max 5 par utilisateur)') . '
                ' . $this->featureItem('Masquage des données sensibles (mot de passe, tokens)') . '
                ' . $this->featureItem('Protection de route basée sur les rôles') . '
                ' . $this->featureItem('Vérification des permissions de ressources') . '
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">17. Fonctionnalités API</div>
        <div class="subsection">
            <div class="subsection-title">Points de Terminaison REST API</div>
            <div class="feature-list">
                ' . $this->featureItem('API de liste d\'événements publics') . '
                ' . $this->featureItem('API de détails d\'événement') . '
                ' . $this->featureItem('API de liste d\'hôtels (par événement)') . '
                ' . $this->featureItem('API de détails d\'hôtel') . '
                ' . $this->featureItem('API de liste de vols (par événement)') . '
                ' . $this->featureItem('API de détails de vol') . '
                ' . $this->featureItem('API de liste d\'aéroports') . '
                ' . $this->featureItem('API de liste de partenaires') . '
                ' . $this->featureItem('API de contenu d\'événement (conditions, info, FAQ)') . '
                ' . $this->featureItem('API de réservation authentifiée') . '
                ' . $this->featureItem('API de portefeuille utilisateur') . '
                ' . $this->featureItem('API de voucher (réservations payées uniquement)') . '
                ' . $this->featureItem('API d\'abonnement à la newsletter') . '
                ' . $this->featureItem('API de mode maintenance') . '
                ' . $this->featureItem('Format de réponse API standardisé') . '
                ' . $this->featureItem('Gestion des erreurs et validation') . '
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">18. Maintenance et Système</div>
        <div class="subsection">
            <div class="subsection-title">Fonctionnalités Système</div>
            <div class="feature-list">
                ' . $this->featureItem('Bascule du mode maintenance') . '
                ' . $this->featureItem('Fonctionnalité de vidage du cache') . '
                ' . $this->featureItem('Migrations de base de données (88 fichiers de migration)') . '
                ' . $this->featureItem('Seeders de base de données') . '
                ' . $this->featureItem('Commandes Artisan pour la création d\'admin') . '
                ' . $this->featureItem('Support de localisation (Français/Anglais)') . '
                ' . $this->featureItem('Support multilingue') . '
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">19. Interface Utilisateur</div>
        <div class="subsection">
            <div class="subsection-title">Interface Admin du Tableau de Bord</div>
            <div class="feature-list">
                ' . $this->featureItem('Interface admin basée sur des modèles Blade') . '
                ' . $this->featureItem('Style TailwindCSS') . '
                ' . $this->featureItem('Composants Shadcn UI') . '
                ' . $this->featureItem('Design responsive') . '
                ' . $this->featureItem('Livewire v3 pour les mises à jour en temps réel') . '
                ' . $this->featureItem('Alpine.js pour l\'interactivité') . '
                ' . $this->featureItem('Intégration d\'icônes Lucide') . '
                ' . $this->featureItem('Boîtes de dialogue modales') . '
                ' . $this->featureItem('Tableaux de données avec pagination') . '
                ' . $this->featureItem('Fonctionnalité de recherche et filtrage') . '
                ' . $this->featureItem('Lignes de détails extensibles') . '
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">20. Base de Données et Modèles</div>
        <div class="subsection">
            <div class="subsection-title">Structure de Base de Données</div>
            <div class="feature-list">
                ' . $this->featureItem('23 fichiers de modèles') . '
                ' . $this->featureItem('88 fichiers de migration') . '
                ' . $this->featureItem('Relations Eloquent (hasMany, belongsTo, hasOne)') . '
                ' . $this->featureItem('Événements et observateurs de modèles') . '
                ' . $this->featureItem('Support des suppressions logiques') . '
                ' . $this->featureItem('Suivi des horodatages') . '
                ' . $this->featureItem('Support de colonnes JSON') . '
            </div>
        </div>
    </div>

    <div style="page-break-before: always; margin-top: 40px; border-top: 3px solid #00adf1; padding-top: 20px;">
        <h2 style="color: #00adf1; text-align: center; font-size: 22pt; margin-bottom: 30px;">FONCTIONNALITÉS FRONTEND</h2>
    </div>

    <div class="section">
        <div class="section-title">21. Authentification Frontend et Gestion des Utilisateurs</div>
        <div class="subsection">
            <div class="subsection-title">Système d\'Authentification</div>
            <div class="feature-list">
                ' . $this->featureItem('React Router DOM pour le routage côté client') . '
                ' . $this->featureItem('Authentification par token Laravel Sanctum pour les requêtes API') . '
                ' . $this->featureItem('Inscription utilisateur avec validation de formulaire') . '
                ' . $this->featureItem('Connexion utilisateur avec stockage de token (localStorage)') . '
                ' . $this->featureItem('Validation du mot de passe (min 8 caractères, majuscule, minuscule, chiffre)') . '
                ' . $this->featureItem('Gestion des tokens (injection automatique dans les requêtes API)') . '
                ' . $this->featureItem('Fonctionnalité de déconnexion avec révocation de token') . '
                ' . $this->featureItem('Page de mise à jour du profil utilisateur') . '
                ' . $this->featureItem('Fonctionnalité de changement de mot de passe') . '
                ' . $this->featureItem('Routes protégées (authentification requise)') . '
                ' . $this->featureItem('Gestion automatique du rafraîchissement de token') . '
                ' . $this->featureItem('Détection d\'expiration de token') . '
            </div>
        </div>
        <div class="subsection">
            <div class="subsection-title">Gestion de Session Utilisateur</div>
            <div class="feature-list">
                ' . $this->featureItem('Persistance du token dans localStorage') . '
                ' . $this->featureItem('Injection automatique du token dans les en-têtes API') . '
                ' . $this->featureItem('Gestion de l\'état de session avec les hooks React') . '
                ' . $this->featureItem('Mise en cache et rafraîchissement des données utilisateur') . '
                ' . $this->featureItem('Déconnexion avec révocation du token backend') . '
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">22. Affichage des Événements et Hébergements</div>
        <div class="subsection">
            <div class="subsection-title">Liste et Navigation des Événements</div>
            <div class="feature-list">
                ' . $this->featureItem('Page d\'accueil avec carrousel/grille d\'événements') . '
                ' . $this->featureItem('Composants de carte d\'événement avec images et détails') . '
                ' . $this->featureItem('Fonctionnalité de recherche d\'événements') . '
                ' . $this->featureItem('Filtrage et tri des événements') . '
                ' . $this->featureItem('Routage dynamique des événements (/:eventSlug)') . '
                ' . $this->featureItem('Page d\'atterrissage d\'événement avec section hero') . '
                ' . $this->featureItem('Menu de navigation d\'événement (Conditions, FAQ, Info)') . '
                ' . $this->featureItem('Pages de contenu d\'événement (Conditions, FAQ, Informations Générales)') . '
                ' . $this->featureItem('Navigation par fil d\'Ariane') . '
                ' . $this->featureItem('Affichage du logo et de la bannière d\'événement') . '
            </div>
        </div>
        <div class="subsection">
            <div class="subsection-title">Gestion du Contenu des Événements</div>
            <div class="feature-list">
                ' . $this->featureItem('Rendu de contenu texte enrichi (HTML sanitisé)') . '
                ' . $this->featureItem('Support de contenu multilingue') . '
                ' . $this->featureItem('Pages de contenu spécifiques aux événements') . '
                ' . $this->featureItem('Composant de rendu HTML sécurisé (protection XSS)') . '
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">23. Affichage et Réservation d\'Hôtels</div>
        <div class="subsection">
            <div class="subsection-title">Navigation des Hôtels</div>
            <div class="feature-list">
                ' . $this->featureItem('Page de liste des hôtels (/:eventSlug/hotels)') . '
                ' . $this->featureItem('Composants de carte d\'hôtel avec images') . '
                ' . $this->featureItem('Affichage en carrousel d\'hôtels') . '
                ' . $this->featureItem('Vue en grille/liste d\'hôtels') . '
                ' . $this->featureItem('Page de détails d\'hôtel (/:eventSlug/hotels/:hotelId)') . '
                ' . $this->featureItem('Galeries d\'images d\'hôtel avec carrousel') . '
                ' . $this->featureItem('Affichage des forfaits hôteliers') . '
                ' . $this->featureItem('Informations de tarification (HT/TTC)') . '
                ' . $this->featureItem('Affichage de la disponibilité') . '
                ' . $this->featureItem('Localisation et description de l\'hôtel') . '
            </div>
        </div>
        <div class="subsection">
            <div class="subsection-title">Système de Réservation d\'Hôtel</div>
            <div class="feature-list">
                ' . $this->featureItem('Composant de formulaire de réservation avec validation') . '
                ' . $this->featureItem('Capture d\'informations client (nom, email, téléphone, entreprise)') . '
                ' . $this->featureItem('Sélection des dates d\'arrivée et de départ') . '
                ' . $this->featureItem('Sélection du nombre d\'invités') . '
                ' . $this->featureItem('Saisie des noms des résidents (jusqu\'à 2 résidents)') . '
                ' . $this->featureItem('Champ d\'instructions/requêtes spéciales') . '
                ' . $this->featureItem('Saisie d\'informations de vol (optionnelle)') . '
                ' . $this->featureItem('Liaison de référence de réservation (lien vers une réservation de vol existante)') . '
                ' . $this->featureItem('Page de confirmation de réservation') . '
                ' . $this->featureItem('Affichage du statut de réservation') . '
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">24. Affichage et Gestion des Vols</div>
        <div class="subsection">
            <div class="subsection-title">Navigation des Vols</div>
            <div class="feature-list">
                ' . $this->featureItem('Liste des vols sur la page d\'atterrissage d\'événement') . '
                ' . $this->featureItem('Composant carrousel de vols (FlightsSection)') . '
                ' . $this->featureItem('Composants de carte de vol avec détails') . '
                ' . $this->featureItem('Page de détails de vol (/:eventSlug/flights/:flightId)') . '
                ' . $this->featureItem('Affichage de la classe de vol (économique, affaires, première)') . '
                ' . $this->featureItem('Affichage de la catégorie de vol (aller simple, aller-retour)') . '
                ' . $this->featureItem('Détails du vol de départ (date, heure, numéro, aéroports)') . '
                ' . $this->featureItem('Détails du vol de retour (pour aller-retour)') . '
                ' . $this->featureItem('Affichage du lien eTicket') . '
                ' . $this->featureItem('Affichage de la référence de vol') . '
            </div>
        </div>
        <div class="subsection">
            <div class="subsection-title">Visibilité des Prix des Vols</div>
            <div class="feature-list">
                ' . $this->featureItem('Support du basculement de visibilité des prix publics') . '
                ' . $this->featureItem('Affichage conditionnel des prix selon les paramètres d\'événement') . '
                ' . $this->featureItem('Message "Prix sur demande" lorsque les prix sont masqués') . '
                ' . $this->featureItem('Formatage des prix (devise française: €)') . '
                ' . $this->featureItem('Gestion de la visibilité des prix dans les carrousels et pages de détails') . '
            </div>
        </div>
        <div class="subsection">
            <div class="subsection-title">Intégration de Réservation de Vol</div>
            <div class="feature-list">
                ' . $this->featureItem('Champ de référence de réservation de vol dans le formulaire de réservation d\'hôtel') . '
                ' . $this->featureItem('Pré-remplissage automatique des détails de vol depuis la référence') . '
                ' . $this->featureItem('Détails de vol optionnels lorsque la référence est fournie') . '
                ' . $this->featureItem('Validation du numéro de vol') . '
                ' . $this->featureItem('Fonctionnalité de liaison de réservation de vol') . '
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">25. Système de Réservation Frontend</div>
        <div class="subsection">
            <div class="subsection-title">Gestion des Réservations</div>
            <div class="feature-list">
                ' . $this->featureItem('Création de réservation via API (utilisateurs authentifiés)') . '
                ' . $this->featureItem('Formulaire de réservation avec validation complète') . '
                ' . $this->featureItem('Affichage automatique de la référence de réservation') . '
                ' . $this->featureItem('Suivi du statut de réservation (en attente, confirmée, payée, annulée, remboursée)') . '
                ' . $this->featureItem('Gestion des informations client') . '
                ' . $this->featureItem('Sélection de date avec validation') . '
                ' . $this->featureItem('Gestion du nombre d\'invités') . '
                ' . $this->featureItem('Gestion des noms des résidents') . '
                ' . $this->featureItem('Champ d\'instructions spéciales') . '
                ' . $this->featureItem('Intégration des informations de vol') . '
            </div>
        </div>
        <div class="subsection">
            <div class="subsection-title">Fonctionnalités du Formulaire de Réservation</div>
            <div class="feature-list">
                ' . $this->featureItem('Validation de formulaire en temps réel') . '
                ' . $this->featureItem('Sanitisation des entrées (conforme OWASP)') . '
                ' . $this->featureItem('Indicateurs de champs requis') . '
                ' . $this->featureItem('Affichage des messages d\'erreur') . '
                ' . $this->featureItem('Confirmation de succès') . '
                ' . $this->featureItem('Acceptation des conditions générales') . '
                ' . $this->featureItem('Recherche de référence de réservation') . '
                ' . $this->featureItem('Exigence conditionnelle des détails de vol') . '
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">26. Tableau de Bord Utilisateur</div>
        <div class="subsection">
            <div class="subsection-title">Fonctionnalités du Tableau de Bord</div>
            <div class="feature-list">
                ' . $this->featureItem('Affichage du profil utilisateur') . '
                ' . $this->featureItem('Affichage du solde du portefeuille (formatage de devise française)') . '
                ' . $this->featureItem('Liste de l\'historique des réservations') . '
                ' . $this->featureItem('Affichage du statut de réservation') . '
                ' . $this->featureItem('Vue des détails de réservation') . '
                ' . $this->featureItem('Liste des vouchers (réservations payées uniquement)') . '
                ' . $this->featureItem('Fonctionnalité de téléchargement de voucher') . '
                ' . $this->featureItem('Sections de téléchargement de documents') . '
            </div>
        </div>
        <div class="subsection">
            <div class="subsection-title">Système de Téléchargement de Documents</div>
            <div class="feature-list">
                ' . $this->featureItem('Téléchargement de document de paiement (Ordre de paiement)') . '
                ' . $this->featureItem('Téléchargement de billet de vol') . '
                ' . $this->featureItem('Support d\'images et PDF') . '
                ' . $this->featureItem('Validation de fichier (type, taille)') . '
                ' . $this->featureItem('Indication de progression du téléchargement') . '
                ' . $this->featureItem('Retour de succès/erreur') . '
                ' . $this->featureItem('Support de téléchargement par réservation') . '
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">27. Système d\'Usurpation d\'Identité Admin</div>
        <div class="subsection">
            <div class="subsection-title">Fonctionnalités d\'Usurpation d\'Identité</div>
            <div class="feature-list">
                ' . $this->featureItem('Détection de token depuis le paramètre de requête URL') . '
                ' . $this->featureItem('Authentification automatique avec token d\'usurpation d\'identité') . '
                ' . $this->featureItem('Affichage de bannière d\'usurpation d\'identité (bannière d\'avertissement jaune)') . '
                ' . $this->featureItem('Affichage du nom d\'utilisateur usurpé') . '
                ' . $this->featureItem('Fonctionnalité d\'arrêt d\'usurpation d\'identité') . '
                ' . $this->featureItem('Suppression de token de l\'URL (mesure de sécurité)') . '
                ' . $this->featureItem('Redirection backend lors de l\'arrêt d\'usurpation d\'identité') . '
                ' . $this->featureItem('Suivi du statut d\'usurpation d\'identité') . '
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">28. Fonctionnalités de Sécurité Frontend (Conformité OWASP)</div>
        <div class="subsection">
            <div class="subsection-title">Sanitisation des Entrées</div>
            <div class="feature-list">
                ' . $this->featureItem('Sanitisation de chaîne (entités HTML, octets null, limites de longueur)') . '
                ' . $this->featureItem('Sanitisation d\'email (validation de format, caractères dangereux)') . '
                ' . $this->featureItem('Sanitisation de téléphone (suppression de non-chiffres, limites de longueur)') . '
                ' . $this->featureItem('Sanitisation d\'URL (caractères dangereux, limites de longueur)') . '
                ' . $this->featureItem('Sanitisation de nombre (suppression de non-numériques, validation de plage)') . '
                ' . $this->featureItem('Sanitisation de date (validation de format, vérification de date réelle)') . '
                ' . $this->featureItem('Sanitisation HTML (intégration DOMPurify)') . '
                ' . $this->featureItem('Sanitisation de données de formulaire (sanitisation en masse avec schéma)') . '
            </div>
        </div>
        <div class="subsection">
            <div class="subsection-title">Validation des Entrées</div>
            <div class="feature-list">
                ' . $this->featureItem('Validation d\'email (format, domaine)') . '
                ' . $this->featureItem('Validation de téléphone (format, longueur)') . '
                ' . $this->featureItem('Validation de mot de passe (exigences de force)') . '
                ' . $this->featureItem('Validation de date (format, plage)') . '
                ' . $this->featureItem('Validation de fichier (type, taille, extension)') . '
                ' . $this->featureItem('Validation de formulaire (côté client, en temps réel)') . '
            </div>
        </div>
        <div class="subsection">
            <div class="subsection-title">Prévention XSS</div>
            <div class="feature-list">
                ' . $this->featureItem('Composant SafeHTML (remplace dangerouslySetInnerHTML)') . '
                ' . $this->featureItem('Intégration DOMPurify pour la sanitisation HTML') . '
                ' . $this->featureItem('Support de Content Security Policy') . '
                ' . $this->featureItem('Échappement HTML automatique dans les formulaires') . '
            </div>
        </div>
        <div class="subsection">
            <div class="subsection-title">Protection CSRF</div>
            <div class="feature-list">
                ' . $this->featureItem('Composant CSRFInitializer pour la gestion des tokens') . '
                ' . $this->featureItem('Récupération automatique du cookie CSRF') . '
                ' . $this->featureItem('Injection de token CSRF dans les requêtes') . '
                ' . $this->featureItem('Intégration CSRF Sanctum') . '
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">29. Internationalisation (i18n)</div>
        <div class="subsection">
            <div class="subsection-title">Support Multilingue</div>
            <div class="feature-list">
                ' . $this->featureItem('Français (fr) comme langue par défaut') . '
                ' . $this->featureItem('Support de la langue anglaise (en)') . '
                ' . $this->featureItem('Composant de changement de langue dans la barre de navigation') . '
                ' . $this->featureItem('Traduction de contenu dynamique') . '
                ' . $this->featureItem('Fichiers de traduction (messages/fr.json, messages/en.json)') . '
                ' . $this->featureItem('Hook useI18n pour les traductions') . '
                ' . $this->featureItem('Persistance de locale dans localStorage') . '
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">30. Composants UI/UX</div>
        <div class="subsection">
            <div class="subsection-title">Composants de Mise en Page</div>
            <div class="feature-list">
                ' . $this->featureItem('Navbar avec design responsive') . '
                ' . $this->featureItem('Footer avec liens et informations') . '
                ' . $this->featureItem('MainLayout composant wrapper') . '
                ' . $this->featureItem('EventNavbar pour la navigation spécifique aux événements') . '
                ' . $this->featureItem('Breadcrumbs composant de navigation') . '
                ' . $this->featureItem('Bouton WhatsApp (bouton d\'action flottant)') . '
            </div>
        </div>
        <div class="subsection">
            <div class="subsection-title">Composants de Formulaire</div>
            <div class="feature-list">
                ' . $this->featureItem('Input composant avec validation') . '
                ' . $this->featureItem('SearchField composant avec icônes') . '
                ' . $this->featureItem('DatePicker composant') . '
                ' . $this->featureItem('GuestsDropdown composant') . '
                ' . $this->featureItem('BookingForm composant (complet)') . '
            </div>
        </div>
        <div class="subsection">
            <div class="subsection-title">Composants d\'Affichage</div>
            <div class="feature-list">
                ' . $this->featureItem('Card composants (Événement, Hôtel, Vol, Forfait)') . '
                ' . $this->featureItem('Carousel composants (Événements, Hôtels, Vols)') . '
                ' . $this->featureItem('ImageCarousel pour les galeries') . '
                ' . $this->featureItem('Badge composant pour le statut') . '
                ' . $this->featureItem('Spinner composant de chargement') . '
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">31. Intégration API</div>
        <div class="subsection">
            <div class="subsection-title">Client API</div>
            <div class="feature-list">
                ' . $this->featureItem('Client API centralisé (lib/api.js)') . '
                ' . $this->featureItem('Client HTTP basé sur Axios') . '
                ' . $this->featureItem('Injection automatique de token') . '
                ' . $this->featureItem('Gestion des cookies CSRF') . '
                ' . $this->featureItem('Gestion d\'erreur et logique de nouvelle tentative') . '
                ' . $this->featureItem('Intercepteurs de requête/réponse') . '
            </div>
        </div>
        <div class="subsection">
            <div class="subsection-title">Intégration des Points de Terminaison API</div>
            <div class="feature-list">
                ' . $this->featureItem('API de liste d\'événements publics') . '
                ' . $this->featureItem('API de détails d\'événement') . '
                ' . $this->featureItem('API de liste d\'hôtels (par événement)') . '
                ' . $this->featureItem('API de liste de vols (par événement)') . '
                ' . $this->featureItem('API de réservation authentifiée') . '
                ' . $this->featureItem('API de portefeuille utilisateur') . '
                ' . $this->featureItem('API de voucher (réservations payées uniquement)') . '
                ' . $this->featureItem('API de téléchargement de document') . '
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">32. Recherche et Filtrage</div>
        <div class="subsection">
            <div class="subsection-title">Fonctionnalité de Recherche</div>
            <div class="feature-list">
                ' . $this->featureItem('Recherche d\'événements sur la page d\'accueil') . '
                ' . $this->featureItem('Recherche d\'hôtels par événement') . '
                ' . $this->featureItem('Recherche de plage de dates (arrivée, départ)') . '
                ' . $this->featureItem('Sélection du nombre d\'invités') . '
                ' . $this->featureItem('Formulaire de recherche avec validation') . '
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">33. Design Responsive</div>
        <div class="subsection">
            <div class="subsection-title">Support d\'Appareils</div>
            <div class="feature-list">
                ' . $this->featureItem('Mises en page responsive pour toutes les pages') . '
                ' . $this->featureItem('Menu mobile de navigation') . '
                ' . $this->featureItem('Composants adaptés au tactile') . '
                ' . $this->featureItem('Formulaires optimisés pour mobile') . '
                ' . $this->featureItem('Carrousels responsive') . '
                ' . $this->featureItem('Mises en page optimisées pour tablette') . '
                ' . $this->featureItem('Mises en page optimisées pour desktop') . '
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">34. Optimisation des Performances</div>
        <div class="subsection">
            <div class="subsection-title">Fonctionnalités d\'Optimisation</div>
            <div class="feature-list">
                ' . $this->featureItem('Division du code avec React Router') . '
                ' . $this->featureItem('Optimisation de la taille du bundle') . '
                ' . $this->featureItem('Tree shaking (Vite)') . '
                ' . $this->featureItem('Composants Spinner pour le chargement') . '
                ' . $this->featureItem('Chargement progressif') . '
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">35. Stack Technologique Frontend</div>
        <div class="subsection">
            <div class="subsection-title">Framework Frontend</div>
            <div class="feature-list">
                ' . $this->featureItem('React 18.2+') . '
                ' . $this->featureItem('React Router DOM 6.20+') . '
                ' . $this->featureItem('Vite 5.0+') . '
                ' . $this->featureItem('Axios (Latest)') . '
                ' . $this->featureItem('DOMPurify (Latest)') . '
                ' . $this->featureItem('React Helmet Async (Latest)') . '
                ' . $this->featureItem('Embla Carousel (Latest)') . '
                ' . $this->featureItem('TailwindCSS pour le style') . '
            </div>
        </div>
    </div>';
    }
}
