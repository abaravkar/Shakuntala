<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
return new class extends Migration {
    public function up(): void {
        Schema::create('franchise_models', function(Blueprint $t) {
            $t->id(); $t->string('name'); $t->string('space'); $t->string('investment');
            $t->text('summary'); $t->text('products'); $t->text('costs')->nullable(); $t->text('notes')->nullable();
            $t->boolean('published')->default(false); $t->unsignedInteger('sort_order')->default(0); $t->timestamps();
        });
        $models = [
            ['Kulfi Cart · Model A','12 sq ft · 3 × 4 ft','₹1.87 lakh','A compact format focused on kulfi and matka kulfi.','Kulfi · 6–12 flavours\nMatka kulfi\nIndicative selling prices: ₹20–₹60; matka ₹45–₹60','Kulfi cart | ₹40,000\nBranding | ₹10,000\nInverter | ₹10,000\nGlycol freezer · 200 L | ₹45,000\nOpening stock | ₹32,000\nWorking capital | ₹50,000','Estimated setup total: ₹1,87,000.'],
            ['Kulfi Cart · Model B','18–21 sq ft · 3 × 6–7 ft','₹2.60 lakh','A larger cart or mini kiosk with a broader dessert selection.','Rabdi Mastani\nKulfi · 6 flavours\nFalooda','Kulfi cart | ₹60,000\nBranding | ₹20,000\nInverter | ₹10,000\nGlycol freezer · 100 L | ₹38,000\nHardtop freezer · 200 L | ₹30,000\nOpening stock | ₹32,000\nWorking capital | ₹70,000','The notes state 18 sq ft; a 3 × 7 ft layout needs 21 sq ft. Mastani flavour count and stock capacity require confirmation.'],
            ['Kiosk Model','About 100 sq ft','₹5.30–₹5.80 lakh','A mini ice cream parlour with room for an expanded menu.','Kulfi · 12 flavours\nIce cream · 18 flavours\nMastani · 10 flavours\nFalooda · 3 flavours\nScope for new offerings','Kiosk structure | ₹2,00,000–₹2,50,000\nBranding | ₹80,000\nFreezer units · 4 | ₹1,30,000\nOther equipment | ₹20,000\nWorking capital | ₹1,00,000','The handwritten total is ₹5.30 lakh. The upper structure estimate brings the total to ₹5.80 lakh.'],
            ['Ice Cream Parlour','About 250 sq ft','₹8–₹10 lakh','A full parlour format with interiors, furniture and a branded customer experience.','Product assortment to be confirmed','Interiors | Included in estimate\nFurniture | Included in estimate\nLighting & fittings | Included in estimate\nFreezer units · 4 | Included in estimate\nBranding | Included in estimate','The area is read as 250 sq ft from the handwritten notes and needs confirmation. An itemised cost breakdown is not yet provided.']
        ];
        foreach($models as $i=>$m) DB::table('franchise_models')->insert(array_combine(['name','space','investment','summary','products','costs','notes'], array_map(fn($v)=>str_replace('\\n',"\n",$v),$m))+['published'=>true,'sort_order'=>$i+1,'created_at'=>now(),'updated_at'=>now()]);
    }
    public function down(): void { Schema::dropIfExists('franchise_models'); }
};
