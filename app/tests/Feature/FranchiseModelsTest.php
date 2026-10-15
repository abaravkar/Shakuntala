<?php
namespace Tests\Feature;
use App\Models\FranchiseModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
class FranchiseModelsTest extends TestCase {
    use RefreshDatabase;
    public function test_models_are_seeded_and_only_published_models_are_public(): void {
        $this->assertSame(4,FranchiseModel::count());
        $model=FranchiseModel::first();
        $this->get('/franchise')->assertOk()->assertSee($model->name)->assertSee('₹5.30–₹5.80 lakh');
        $model->update(['published'=>false]);
        $this->get('/franchise')->assertOk()->assertDontSee($model->name);
    }
    public function test_admin_can_create_edit_and_hide_models_but_visitors_cannot(): void {
        $this->get('/admin/franchise-models')->assertRedirect('/admin/login');
        $data=['name'=>'New format','space'=>'50 sq ft','investment'=>'₹3 lakh','summary'=>'Test format','products'=>'Kulfi','costs'=>'Cart | ₹50,000','notes'=>'Estimate','sort_order'=>5,'published'=>1];
        $this->post('/admin/franchise-models',$data)->assertRedirect('/admin/login');
        $this->actingAs(User::factory()->create(['is_admin'=>false]))->get('/admin/franchise-models')->assertForbidden();
        $this->actingAs(User::factory()->create(['is_admin'=>true]));
        $this->post('/admin/franchise-models',$data)->assertRedirect('/admin/franchise-models');
        $model=FranchiseModel::where('name','New format')->firstOrFail();
        $this->get('/admin/franchise-models/'.$model->id.'/edit')->assertOk();
        $this->put('/admin/franchise-models/'.$model->id,$data+[])->assertRedirect();
        $this->put('/admin/franchise-models/'.$model->id,array_replace($data,['published'=>0]))->assertRedirect();
        $this->get('/franchise')->assertDontSee('New format');
        $this->post('/admin/franchise-models',array_replace($data,['sort_order'=>-1]))->assertSessionHasErrors('sort_order');
    }
}
