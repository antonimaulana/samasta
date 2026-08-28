<?php



namespace App\Http\Controllers;



use App\Models\Taman;

use App\Support\NearbyTamanPaginator;

use App\Support\TamanMapData;

use Illuminate\Http\Request;

use Illuminate\View\View;



class TamanController extends Controller

{

    private const POPULAR_FASILITAS = Taman::FASILITAS_DAFTAR;



    public function index(Request $request): View

    {

        ['query' => $query, 'search' => $search, 'selectedFasilitas' => $selectedFasilitas, 'selectedKategori' => $selectedKategori] = $this->buildFilteredQuery($request);



        $nearbyMode = $request->boolean('nearby') && $request->filled(['lat', 'lng']);

        $userLat = null;

        $userLng = null;



        if ($nearbyMode) {

            $validated = $request->validate([

                'lat' => ['required', 'numeric', 'between:-90,90'],

                'lng' => ['required', 'numeric', 'between:-180,180'],

            ]);



            $userLat = (float) $validated['lat'];

            $userLng = (float) $validated['lng'];



            $tamans = NearbyTamanPaginator::paginate($query, $userLat, $userLng, $request);

        } else {

            $tamans = $query->paginate(12)->withQueryString();

        }



        return view('tamans.index', [

            'tamans' => $tamans,

            'search' => $search,

            'selectedFasilitas' => $selectedFasilitas,

            'popularFasilitas' => self::POPULAR_FASILITAS,

            'nearbyMode' => $nearbyMode,

            'userLat' => $userLat,

            'userLng' => $userLng,

        ]);

    }



    public function map(Request $request): View

    {

        ['query' => $query, 'search' => $search, 'selectedFasilitas' => $selectedFasilitas, 'selectedKategori' => $selectedKategori] = $this->buildFilteredQuery($request);



        $cacheKey = md5(json_encode([

            $search,

            $selectedKategori,

            $selectedFasilitas,

        ]));



        $mapPoints = TamanMapData::mapPoints(clone $query, $cacheKey);



        return view('tamans.map', [

            'tamans' => $mapPoints,

            'mapPoints' => $mapPoints,

            'search' => $search,

            'selectedFasilitas' => $selectedFasilitas,

            'selectedKategori' => $selectedKategori,

            'popularFasilitas' => self::POPULAR_FASILITAS,

            'kategoris' => Taman::KATEGORI,

        ]);

    }



    public function show(Taman $taman): View

    {

        $taman->load('images');



        return view('tamans.show', compact('taman'));

    }



    /**

     * @return array{query: \Illuminate\Database\Eloquent\Builder, search: string, selectedFasilitas: list<string>, selectedKategori: ?string}

     */

    private function buildFilteredQuery(Request $request): array

    {

        $search = trim((string) $request->input('search', ''));

        $selectedFasilitas = array_values(array_filter((array) $request->input('fasilitas', [])));

        $selectedKategori = $request->filled('kategori') ? (string) $request->input('kategori') : null;



        $query = Taman::query()->with('images')->latest();



        if ($search !== '') {

            $query->where('nama_taman', 'LIKE', '%'.$search.'%');

        }



        if ($selectedKategori && in_array($selectedKategori, Taman::KATEGORI, true)) {

            $query->where('kategori', $selectedKategori);

        }



        foreach ($selectedFasilitas as $fasilitas) {

            $query->where(function ($builder) use ($fasilitas) {

                foreach ($this->fasilitasSearchTerms($fasilitas) as $term) {

                    $builder->orWhereRaw('LOWER(fasilitas) LIKE ?', ['%'.mb_strtolower($term).'%']);

                }

            });

        }



        return compact('query', 'search', 'selectedFasilitas', 'selectedKategori');

    }



    /**

     * @return list<string>

     */

    private function fasilitasSearchTerms(string $label): array
    {
        if (in_array($label, Taman::FASILITAS_DAFTAR, true)) {
            return [$label];
        }

        return [$label];
    }

}


