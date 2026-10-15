<?php

namespace Database\Seeders;

use App\Models\ContentItem;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $products = [
            ['rabdi-mastani', 'रबडी मस्तानी', 'रबड़ी मस्तानी', 'Rabdi Mastani', 50, 'rabadi-mastani.webp', '15 flavours · small to 1 kg', '१५ प्रकार · लहान ते १ किलो', '१५ स्वाद · छोटे से १ किलो'],
            ['mastani', 'मस्तानी', 'मस्तानी', 'Mastani', 100, 'mastani.webp', 'With ice cream', 'आइसक्रीमसह', 'आइसक्रीम के साथ'],
            ['kulfi', 'कुल्फी', 'कुल्फी', 'Kulfi & Matka Kulfi', 20, 'kulfi.webp', 'Traditional frozen favourites', 'पारंपरिक थंड गोडवा', 'पारंपरिक ठंडी मिठास'],
            ['ice-cream', 'आइसक्रीम', 'आइसक्रीम', 'Ice Cream', 30, 'icerem.webp', 'A range of favourite flavours', 'आवडत्या चवींचे पर्याय', 'पसंदीदा स्वाद'],
            ['falooda', 'फालुदा', 'फालूदा', 'Falooda', 100, 'faluda.webp', 'Royal and fruit specials', 'रॉयल आणि फ्रूट स्पेशल', 'रॉयल और फ्रूट स्पेशल'],
            ['cold-coffee', 'कोल्ड कॉफी', 'कोल्ड कॉफी', 'Cold Coffee', 70, 'coldcoffee.webp', 'Coffee and ice cream combinations', 'कॉफी आणि आइसक्रीम', 'कॉफी और आइसक्रीम'],
        ];
        foreach ($products as $i => $p) {
            ContentItem::firstOrCreate(['slug' => $p[0]], ['type' => 'product', 'title' => ['mr' => $p[1], 'hi' => $p[2], 'en' => $p[3]], 'body' => ['en' => $p[6], 'mr' => $p[7], 'hi' => $p[8]], 'price' => $p[4], 'image' => $p[5], 'status' => 'published', 'sort_order' => $i]);
        }
        $branches = [
            ['baramati-bhigwan', 'हॉटेल अक्षय गार्डन शेजारी', 'होटल अक्षय गार्डन के पास', 'Near Hotel Akshay Garden', 'भिगवण रोड, बारामती', 'भिगवन रोड, बारामती', 'Bhigwan Road, Baramati', 'reported'],
            ['baramati-subhash', 'सुभाष चौक', 'सुभाष चौक', 'Subhash Chowk', 'मारवाड पेठ, बारामती', 'मारवाड पेठ, बारामती', 'Marwad Peth, Baramati', 'reported'],
            ['jejuri-highway', 'मानकर डोसा शेजारी', 'मानकर डोसा के पास', 'Near Mankar Dosa', 'पालखी महामार्ग, जेजुरी', 'पालखी महामार्ग, जेजुरी', 'Palkhi Highway, Jejuri', 'reported'],
            ['jejuri-nandi', 'नंदी चौक', 'नंदी चौक', 'Nandi Chowk', 'जेजुरी गड पायथा', 'जेजुरी गढ़ के नीचे', 'Foothills of Jejuri Gad', 'reported'],
            ['charholi', 'चरोली फाटा', 'चरोली फाटा', 'Charholi Phata', 'चरोली, पुणे', 'चरोली, पुणे', 'Charholi, Pune', 'reported'],
            ['chikhali', 'शिव रोड', 'शिव रोड', 'Shiv Road', 'चिखली, पिंपरी चिंचवड', 'चिखली, पिंपरी चिंचवड', 'Chikhali, Pimpri-Chinchwad', 'reported'],
            ['bhosari', 'केंद्रीय विहार', 'केंद्रीय विहार', 'Kendriya Vihar', 'स्पाईन सिटी रोड चौक, भोसरी', 'स्पाईन सिटी रोड चौक, भोसरी', 'Spine City Road Chowk, Bhosari', 'reported'],
            ['pune-laxmi', 'पत्र्या मारुती चौक', 'पत्र्या मारुती चौक', 'Patrya Maruti Chowk', 'लक्ष्मी रोड, पुणे', 'लक्ष्मी रोड, पुणे', 'Laxmi Road, Pune', 'reported'],
            ['kunjirwadi', 'कुंजीरवाडी', 'कुंजीरवाडी', 'Kunjirwadi', '', '', '', 'planned'],
            ['kedgaon', 'केडगाव चौफुला', 'केडगाव चौफुला', 'Kedgaon Chauphula', '', '', '', 'planned'],
            ['patas', 'पाटस', 'पाटस', 'Patas', '', '', '', 'planned'],
            ['dhanori', 'धानोरी', 'धानोरी', 'Dhanori', 'पुणे', 'पुणे', 'Pune', 'unverified'],
            ['phaltan', 'फलटण', 'फलटण', 'Phaltan', '', '', '', 'closed'],
            ['indapur', 'इंदापूर', 'इंदापूर', 'Indapur', '', '', '', 'closed'],
        ];
        foreach ($branches as $i => $b) {
            ContentItem::firstOrCreate(['slug' => $b[0]], ['type' => 'branch', 'title' => ['mr' => $b[1], 'hi' => $b[2], 'en' => $b[3]], 'body' => ['mr' => $b[4], 'hi' => $b[5], 'en' => $b[6]], 'status' => $b[7], 'sort_order' => $i]);
        }
        foreach (['Instagram', 'YouTube', 'Facebook', 'LinkedIn'] as $i => $platform) {
            ContentItem::firstOrCreate(['slug' => strtolower($platform)], ['type' => 'social', 'title' => array_fill_keys(['mr', 'hi', 'en'], $platform), 'body' => ['mr' => 'अधिकृत लिंक लवकरच', 'hi' => 'आधिकारिक लिंक जल्द', 'en' => 'Official profile pending'], 'status' => 'draft', 'sort_order' => $i]);
        }
        foreach (['customer', 'celebration', 'banner', 'opening'] as $i => $image) {
            ContentItem::firstOrCreate(['slug' => 'gallery-'.$image], ['type' => 'gallery', 'title' => ['mr' => 'शकुंतलाचे क्षण', 'hi' => 'शकुंतला के पल', 'en' => 'Shakuntala moments'], 'body' => ['mr' => 'दिलेली ब्रँड छायाचित्रे', 'hi' => 'प्राप्त ब्रांड तस्वीरें', 'en' => 'Supplied brand photography'], 'image' => $image.'.webp', 'status' => 'published', 'sort_order' => $i]);
        }
        ContentItem::firstOrCreate(['slug' => 'welcome'], ['type' => 'blog', 'title' => ['mr' => 'शकुंतलाचे गोड विश्व', 'hi' => 'शकुंतला की मीठी दुनिया', 'en' => 'The sweet world of Shakuntala'], 'body' => ['mr' => 'रबडी मस्तानी, कुल्फी आणि फालुदा यांचा आस्वाद घ्या. मेनू आणि शाखेची माहिती पाहा.', 'hi' => 'रबड़ी मस्तानी, कुल्फी और फालूदा के स्वाद खोजें। मेन्यू और शाखाओं की जानकारी देखें।', 'en' => 'Explore Rabdi Mastani, kulfi and falooda. Browse our menu and supplied outlet information.'], 'status' => 'draft', 'sort_order' => 0]);
    }
}
