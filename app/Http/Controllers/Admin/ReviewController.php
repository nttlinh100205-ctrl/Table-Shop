<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;
class ReviewController extends Controller
{
    public function index(Request $request) {
        $f=$request->validate(['satisfaction'=>'nullable|in:happy,neutral,unhappy','status'=>'nullable|in:pending,resolved','q'=>'nullable|string|max:100']);
        $q=Review::with(['user','product']);
        if ($request->filled('satisfaction')) $q->whereBetween('rating',['happy'=>[4,5],'neutral'=>[3,3],'unhappy'=>[1,2]][$f['satisfaction']]);
        if ($request->filled('status')) $q->where('resolution_status',$f['status']);
        if ($request->filled('q')) $q->whereHas('user',fn($u)=>$u->where('name','like','%'.$f['q'].'%')->orWhere('email','like','%'.$f['q'].'%'));
        return view('admin.reviews.index',['reviews'=>$q->latest('id')->paginate(20)->withQueryString(),
            'total'=>Review::count(),'average'=>Review::avg('rating'),'unhappy'=>Review::where('rating','<=',2)->where('resolution_status','pending')->count()]);
    }
    public function update(Request $request, Review $review) {
        $data=$request->validate(['admin_reply'=>'required|string|min:5|max:2000','resolution_status'=>'required|in:pending,resolved']);
        $review->update($data+['replied_at'=>now(), 'reply_source'=>'admin', 'ai_reply_status'=>'skipped']);
        return back()->with('success','Đã lưu phản hồi. Khách có thể xem trong đơn hàng và trang sản phẩm.');
    }
}
