<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LocationController extends Controller
{
	public function fetchDistrict()
{
    $rows = \DB::table('districts')
        ->orderBy('name')
        ->get();

    $html = '<option value="">-- Select District --</option>';

    foreach ($rows as $r) {
        $html .= '<option value="'.$r->code.'">'.$r->name.'</option>';
    }

    return response($html);
}


    // District -> Upozila
    public function fetch_upozila(Request $request)
    {
        $district = $request->district;

        $rows = DB::table('configupozila')
            ->where('district_code', $district)
            ->orderBy('name')
            ->get();

        // Return <option> list
        $html = '<option value="">-- Select Upozila --</option>';
        foreach ($rows as $r) {
            $html .= '<option value="'.$r->code.'">'.$r->name.'</option>';
        }

        return response($html);
    }
	
	// upozila  -> union
	public function fetch_union(Request $request)
    {
        $upozila = $request->upozila;

        $rows = DB::table('configunion')
            ->where('upozila_code', $upozila)
            ->orderBy('name')
            ->get();

        // Return <option> list
        $html = '<option value="">-- Select union --</option>';
        foreach ($rows as $r) {
            $html .= '<option value="'.$r->code.'">'.$r->name.'</option>';
        }

        return response($html);
    }
	

    // union -> Village
    public function fetch_village(Request $request)
    {
        $union = $request->union;

        $rows = DB::table('configvillage')
            ->where('union_code', $union)
            ->orderBy('name')
            ->get();

        $html = '<option value="">-- Select Village --</option>';
        foreach ($rows as $r) {
            $html .= '<option value="'.$r->code.'">'.$r->name.'</option>';
        }

        return response($html);
    }
	
	public function storeDistrict(Request $request)
	{
		$request->validate([
			'name' => 'required|string|max:100'
		]);

		$code = \Str::uuid()->toString();   // or your own code logic

		\DB::table('configdistrict')->insert([
			'code' => $request->name,
			'name' => $request->name,
		]);

		return response()->json([
			'ok'   => true,
			'id'   => $request->name,
			'name' => $request->name
		]);
	}
	public function storeUpozila(Request $request)
		{
			$request->validate([
				'district' => 'required',
				'name'     => 'required|string|max:100'
			]);

			$code = \Str::uuid()->toString();

			\DB::table('configupozila')->insert([
				'code'          => $request->name,
				'district_code'=> $request->district,
				'name'          => $request->name,
			]);

			return response()->json([
				'ok'   => true,
				'id'   => $request->name,
				'name' => $request->name
			]);
		}
	public function storeUnion(Request $request)
		{
			$request->validate([
				'upozila' => 'required',
				'name'    => 'required|string|max:100'
			]);

			$code = \Str::uuid()->toString();

			\DB::table('configunion')->insert([
				'code'        => $request->name,
				'upozila_code'=> $request->upozila,
				'name'        => $request->name,
			]);

			return response()->json([
				'ok'   => true,
				'id'   => $request->name,
				'name' => $request->name
			]);
		}

	public function storeVillage(Request $request)
	{
		$request->validate([
			'union' => 'required',
			'name'  => 'required|string|max:100'
		]);

		$exists = DB::table('configvillage')
			->where('union_code', $request->union)
			->where('name', $request->name)
			->exists();

		if ($exists) {
			return response()->json([
				'ok'      => false,
				'message' => 'This village already exists in the selected union.'
			]);
		}

		try {
			DB::table('configvillage')->insert([
				'code'       => $request->name,
				'union_code' => $request->union,
				'name'       => $request->name,
			]);
		} catch (\Exception $e) {
			return response()->json([
				'ok'      => false,
				'message' => 'Failed to save village. Please try again.'
			], 500);
		}

		return response()->json([
			'ok'   => true,
			'id'   => $request->name,
			'name' => $request->name
		]);
	}

    // Village -> Post
    public function fetch_post(Request $request)
    {
        $village = $request->village;

        $rows = DB::table('configpost')
            ->where('village_code', $village)
            ->orderBy('name')
            ->get();

        $html = '<option value="">-- Select Post --</option>';
        foreach ($rows as $r) {
            $html .= '<option value="'.$r->code.'">'.$r->name.'</option>';
        }

        return response($html);
    }

    public function storePost(Request $request)
    {
        $request->validate([
            'village' => 'required',
            'name'    => 'required|string|max:100'
        ]);

        \DB::table('configpost')->insert([
            'code'         => $request->name,
            'village_code' => $request->village,
            'name'         => $request->name,
        ]);

        return response()->json([
            'ok'   => true,
            'id'   => $request->name,
            'name' => $request->name
        ]);
    }

}

