<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Client;
use App\Models\Work;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Video Editor with 2 years experience
        $videoEditor1 = User::create([
            'name' => 'Alex Chen',
            'username' => 'alexchen',
            'job_title' => 'Video Editor',
            'bio' => 'Passionate video editor specializing in social media content and short-form videos. I create engaging content that captures attention and drives engagement.',
            'expertise' => 'social media content, short-form videos, color grading, motion graphics',
            'skills' => 'Adobe Premiere Pro, After Effects, 2 years of experience in video editing, Instagram Reels, TikTok content creation',
        ]);

        // Add some works for Alex
        Work::create(['user_id' => $videoEditor1->id, 'title' => 'Instagram Reel Series', 'url' => 'https://instagram.com/sample1', 'description' => 'Created viral Instagram Reels series']);
        Work::create(['user_id' => $videoEditor1->id, 'title' => 'Product Launch Video', 'url' => 'https://youtube.com/sample1', 'description' => 'Product launch promotional video']);

        // 2. Video Editor with 2 years experience (different specialty)
        $videoEditor2 = User::create([
            'name' => 'Maria Rodriguez',
            'username' => 'mariarodriguez',
            'job_title' => 'Video Editor',
            'bio' => 'Creative video editor focused on storytelling and brand content. I help businesses tell their story through compelling video content.',
            'expertise' => 'brand storytelling, corporate videos, promotional content, podcast editing',
            'skills' => 'Final Cut Pro, Adobe Premiere Pro, 2 years professional experience, brand content creation, audio editing',
        ]);

        // Add works for Maria
        Work::create(['user_id' => $videoEditor2->id, 'title' => 'Brand Story Documentary', 'url' => 'https://vimeo.com/sample1', 'description' => 'Documentary-style brand story']);
        Work::create(['user_id' => $videoEditor2->id, 'title' => 'Corporate Training Videos', 'url' => 'https://youtube.com/sample2', 'description' => 'Training video series']);

        // 3. Graphic Designer with doctor clients
        $designer1 = User::create([
            'name' => 'David Kim',
            'username' => 'davidkim',
            'job_title' => 'Graphic Designer',
            'bio' => 'Experienced graphic designer specializing in healthcare and medical branding. I create clean, professional designs for medical practices.',
            'expertise' => 'medical branding, healthcare design, logo design, print materials',
            'skills' => 'Adobe Creative Suite, Figma, 4 years experience in graphic design, medical industry knowledge, brand development',
        ]);

        // Add doctor clients for David
        Client::create([
            'user_id' => $designer1->id,
            'name' => 'Dr. Sarah Johnson',
            'job_title' => 'Cardiologist',
            'feedback' => 'David created an amazing brand identity for my cardiology practice. His understanding of medical aesthetics is exceptional.',
            'photo_url' => 'https://example.com/photos/dr-sarah.jpg'
        ]);

        Client::create([
            'user_id' => $designer1->id,
            'name' => 'Dr. Michael Chen',
            'job_title' => 'Pediatrician',
            'feedback' => 'Professional and creative designs that perfectly represent our pediatric clinic. Highly recommended!',
            'photo_url' => 'https://example.com/photos/dr-michael.jpg'
        ]);

        // 4. Content Creator with doctor clients
        $contentCreator = User::create([
            'name' => 'Lisa Thompson',
            'username' => 'lisathompson',
            'job_title' => 'Content Creator',
            'bio' => 'Content creator and social media manager specializing in healthcare and wellness content. I help medical professionals build their online presence.',
            'expertise' => 'healthcare content, social media strategy, medical education content, wellness videos',
            'skills' => 'content strategy, video production, social media management, 3 years experience, medical content creation',
        ]);

        // Add doctor and medical clients for Lisa
        Client::create([
            'user_id' => $contentCreator->id,
            'name' => 'Dr. Amanda White',
            'job_title' => 'Family Doctor',
            'feedback' => 'Lisa helped us create educational content that our patients love. Her medical content expertise is outstanding.',
            'photo_url' => 'https://example.com/photos/dr-amanda.jpg'
        ]);

        Client::create([
            'user_id' => $contentCreator->id,
            'name' => 'Dr. Robert Garcia',
            'job_title' => 'Dermatologist',
            'feedback' => 'Professional content creation that has significantly improved our patient engagement and education.',
            'photo_url' => 'https://example.com/photos/dr-robert.jpg'
        ]);

        // 5. Web Developer
        $developer = User::create([
            'name' => 'James Wilson',
            'username' => 'jameswilson',
            'job_title' => 'Full Stack Developer',
            'bio' => 'Full stack developer with expertise in web applications and e-commerce solutions. I build scalable and user-friendly applications.',
            'expertise' => 'web development, e-commerce, API development, database design',
            'skills' => 'Laravel, React, Node.js, MySQL, 5 years experience in web development, cloud deployment',
        ]);

        // Add some clients for James
        Client::create([
            'user_id' => $developer->id,
            'name' => 'Jennifer Smith',
            'job_title' => 'Business Owner',
            'feedback' => 'James built an excellent e-commerce platform for our business. Professional and reliable developer.',
            'photo_url' => 'https://example.com/photos/jennifer.jpg'
        ]);

        // 6. Marketing Specialist with medical industry experience
        $marketer = User::create([
            'name' => 'Emily Davis',
            'username' => 'emilydavis',
            'job_title' => 'Digital Marketing Specialist',
            'bio' => 'Digital marketing specialist with extensive experience in healthcare marketing. I help medical practices grow their patient base through strategic digital marketing.',
            'expertise' => 'healthcare marketing, digital advertising, SEO, patient acquisition',
            'skills' => 'Google Ads, Facebook Ads, SEO optimization, 4 years experience in digital marketing, healthcare industry knowledge',
        ]);

        // Add medical professional clients for Emily
        Client::create([
            'user_id' => $marketer->id,
            'name' => 'Dr. Kevin Lee',
            'job_title' => 'Orthopedic Surgeon',
            'feedback' => 'Emily\'s marketing strategies have significantly increased our patient inquiries. She understands the medical field perfectly.',
            'photo_url' => 'https://example.com/photos/dr-kevin.jpg'
        ]);

        Client::create([
            'user_id' => $marketer->id,
            'name' => 'Dr. Rachel Brown',
            'job_title' => 'Dentist',
            'feedback' => 'Professional digital marketing services that helped grow our dental practice. Excellent results and communication.',
            'photo_url' => 'https://example.com/photos/dr-rachel.jpg'
        ]);

        // 7. Photographer
        $photographer = User::create([
            'name' => 'Ryan Martinez',
            'username' => 'ryanmartinez',
            'job_title' => 'Professional Photographer',
            'bio' => 'Professional photographer specializing in portrait and commercial photography. I capture moments that tell stories and create lasting impressions.',
            'expertise' => 'portrait photography, commercial photography, event photography, photo editing',
            'skills' => 'Canon EOS R, Lightroom, Photoshop, 6 years experience in professional photography, studio lighting',
        ]);

        // Add some works for Ryan
        Work::create(['user_id' => $photographer->id, 'title' => 'Corporate Headshots', 'url' => 'https://portfolio.com/ryan1', 'description' => 'Professional headshots for executives']);
        Work::create(['user_id' => $photographer->id, 'title' => 'Wedding Photography', 'url' => 'https://portfolio.com/ryan2', 'description' => 'Wedding photography collection']);

        // 8. UX/UI Designer
        $uxDesigner = User::create([
            'name' => 'Sophia Lee',
            'username' => 'sophialee',
            'job_title' => 'UX/UI Designer',
            'bio' => 'UX/UI designer passionate about creating intuitive and beautiful user experiences. I design digital products that users love to interact with.',
            'expertise' => 'user experience design, user interface design, prototyping, user research',
            'skills' => 'Figma, Adobe XD, Sketch, 3 years experience in UX/UI design, user research, prototyping',
        ]);

        // Add some works for Sophia
        Work::create(['user_id' => $uxDesigner->id, 'title' => 'Mobile App Design', 'url' => 'https://dribbble.com/sophia1', 'description' => 'Mobile app UI/UX design']);
        Work::create(['user_id' => $uxDesigner->id, 'title' => 'E-commerce Website Redesign', 'url' => 'https://behance.net/sophia1', 'description' => 'Complete e-commerce website redesign']);

        // Add a client for Sophia
        Client::create([
            'user_id' => $uxDesigner->id,
            'name' => 'Mark Johnson',
            'job_title' => 'Startup Founder',
            'feedback' => 'Sophia designed an incredible user interface for our app. Her attention to detail and user experience expertise is remarkable.',
            'photo_url' => 'https://example.com/photos/mark.jpg'
        ]);
    }
} 