<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\{Prize, SpinHistory};
use Illuminate\Http\Request;
class PrizeController extends Controller
{
    public function index() {
        return view('admin.prizes.index', ['prizes'=>Prize::orderBy('id')->get(),
            'histories'=>SpinHistory::with('user')->latest('id')->paginate(20)]);
    }
    public function create() { return view('admin.prizes.form',['prize'=>new Prize(['type'=>'points','quantity'=>-1,'probability'=>10,'is_active'=>true])]); }
    public function edit(Prize $prize) { return view('admin.prizes.form',compact('prize')); }
    private function validated(Request $request): array {
        $data=$request->validate(['name'=>'required|string|max:60','type'=>'required|in:points,voucher,ticket,empty',
            'value'=>'required|integer|min:0|max:100000000','probability'=>'required|integer|min:0|max:1000000',
            'quantity'=>'required|integer|min:-1|max:1000000','is_active'=>'nullable|boolean']);
        $data['is_active']=$request->boolean('is_active');
        if ($data['type']==='empty') $data['value']=0;
        elseif ((int)$data['value']<1) throw \Illuminate\Validation\ValidationException::withMessages(['value'=>'Giá trị phần thưởng phải lớn hơn 0.']);
        return $data;
    }
    public function store(Request $request) {
        Prize::create($this->validated($request));
        return redirect()->route('admin.prizes.index')->with('success','Đã thêm phần thưởng.');
    }
    public function update(Request $request, Prize $prize) {
        $prize->update($this->validated($request));
        return redirect()->route('admin.prizes.index')->with('success','Đã cập nhật phần thưởng. Lịch sử đã trao được giữ nguyên.');
    }
}
