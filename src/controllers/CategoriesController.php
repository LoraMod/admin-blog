<?php

namespace LaraMod\Admin\Blog\Controllers;

use App\Http\Controllers\Controller;
use LaraMod\Admin\Blog\Models\Categories;
use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;

class CategoriesController extends Controller
{

    private $data = [];

    public function __construct()
    {
        config()->set('admincore.menu.blog.active', true);
    }

    public function index(Request $request)
    {
        if ($request->wantsJson()) {
            return Categories::paginate(20);
        }

        return view('adminblog::categories.list');
    }

    public function getForm(Request $request)
    {
        $this->data['category'] = ($request->has('id') ? Categories::find($request->get('id')) : new Categories());
        if ($request->wantsJson()) {
            return $this->data;
        }

        return view('adminblog::categories.form', $this->data);
    }

    public function postForm(Request $request)
    {

        $category = Categories::firstOrNew(['id' => $request->get('id')]);
        try {
            if(!$request->get('slug')){
                $request->merge(['slug' => $category->createSlug(
                    $request->get('title_'.config('app.fallback_locale', 'en'))
                )]);
            }
            $category->autoFill($request);

        } catch (\Exception $e) {
            return redirect()->back()->withInput()->withErrors(['errors' => $e->getMessage()]);
        }

        return redirect()->route('admin.blog.categories')->with('message', [
            'type' => 'success',
            'text' => 'Category saved.',
        ]);
    }

    public function delete(Request $request)
    {
        if (!$request->has('id')) {
            return redirect()->route('admin.blog.categories')->with('message', [
                'type' => 'danger',
                'text' => 'No ID provided!',
            ]);
        }
        try {
            Categories::find($request->get('id'))->delete();
        } catch (\Exception $e) {
            return redirect()->route('admin.blog.categories')->with('message', [
                'type' => 'danger',
                'text' => $e->getMessage(),
            ]);
        }

        return redirect()->route('admin.blog.categories')->with('message', [
            'type' => 'success',
            'text' => 'Categories moved to trash.',
        ]);
    }

    public function dataTable()
    {
        $items = Categories::select(['id', 'title_'.config('app.fallback_locale', 'en'), 'created_at', 'viewable']);

        return Datatables::of($items)
            ->addColumn('posts_count', function ($item) {
                return $item->posts->count();
            })
            ->addColumn('action', function ($item) {
                return '<a href="' . route('admin.blog.categories.form', ['id' => $item->id]) . '" class="btn btn-success btn-xs"><i class="fa fa-pencil"></i></a>
                                    <a href="' . route('admin.blog.categories.delete',
                        ['id' => $item->id]) . '" class="btn btn-danger btn-xs require-confirm"><i class="fa fa-trash"></i></a>';
            })
            ->addColumn('status', function ($item) {
                return !$item->viewable ? '<i class="fa fa-eye-slash"></i>' : '<i class="fa fa-eye"></i>';
            })
            ->editColumn('created_at', function ($item) {
                return $item->created_at->format('d.m.Y H:i');
            })
            ->orderColumn('created_at $1', 'id $1')
            ->make('true');
    }


}