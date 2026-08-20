<?php



namespace App\Http\Controllers\Admin;



use App\Http\Controllers\Controller;

use App\Models\User;

use Illuminate\Http\RedirectResponse;

use Illuminate\Http\Request;

use Illuminate\Validation\Rule;

use Illuminate\Validation\Rules\Password;

use Illuminate\View\View;



class UserController extends Controller

{

    public function __construct()

    {

        $this->authorizeResource(User::class, 'user');

    }



    public function index(): View

    {

        $users = User::query()->with('timPelaksanas')->orderBy('name')->paginate(15);



        return view('admin.users.index', compact('users'));

    }



    public function create(): View

    {

        return view('admin.users.create');

    }



    public function store(Request $request): RedirectResponse

    {

        $validated = $this->validateUser($request);



        $user = User::create([

            'name' => $validated['name'],

            'email' => $validated['email'],

            'role' => $validated['role'],

            'akses_semua_wilayah' => $validated['akses_semua_wilayah'] ?? false,

            'password' => $validated['password'],

        ]);



        $this->syncOperatorTeams($user, $validated);



        return redirect()

            ->route('admin.users.index')

            ->with('success', 'Pengguna berhasil ditambahkan.');

    }



    public function edit(User $user): View

    {

        $user->load('timPelaksanas');



        return view('admin.users.edit', compact('user'));

    }



    public function update(Request $request, User $user): RedirectResponse

    {

        $validated = $this->validateUser($request, $user);



        $payload = [

            'name' => $validated['name'],

            'email' => $validated['email'],

            'role' => $validated['role'],

            'akses_semua_wilayah' => $validated['akses_semua_wilayah'] ?? false,

        ];



        if (! empty($validated['password'])) {

            $payload['password'] = $validated['password'];

        }



        $user->update($payload);

        $this->syncOperatorTeams($user, $validated);



        return redirect()

            ->route('admin.users.index')

            ->with('success', 'Pengguna berhasil diperbarui.');

    }



    public function destroy(User $user): RedirectResponse

    {

        if ($user->id === auth()->id()) {

            return back()->withErrors(['user' => 'Anda tidak dapat menghapus akun sendiri.']);

        }



        $user->delete();



        return redirect()

            ->route('admin.users.index')

            ->with('success', 'Pengguna berhasil dihapus.');

    }



    /**

     * @param  array<string, mixed>  $validated

     */

    private function syncOperatorTeams(User $user, array $validated): void

    {

        if ($user->role !== User::ROLE_OPERATOR) {

            $user->timPelaksanas()->sync([]);

            $user->update(['akses_semua_wilayah' => false]);



            return;

        }



        if ($validated['akses_semua_wilayah'] ?? false) {

            $user->timPelaksanas()->sync([]);



            return;

        }



        $user->timPelaksanas()->sync($validated['tim_pelaksana_ids'] ?? []);

    }



    /**

     * @return array<string, mixed>

     */

    private function validateUser(Request $request, ?User $user = null): array

    {

        $validated = $request->validate([

            'name' => ['required', 'string', 'max:100'],

            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user?->id)],

            'role' => ['required', Rule::in(array_keys(User::ROLES))],

            'akses_semua_wilayah' => ['nullable', 'boolean'],

            'tim_pelaksana_ids' => ['nullable', 'array'],

            'tim_pelaksana_ids.*' => [

                'integer',

                Rule::exists('tim_pelaksanas', 'id')->where(fn ($q) => $q->where('aktif', true)),

            ],

            'password' => [$user ? 'nullable' : 'required', 'confirmed', Password::defaults()],

        ], [], [

            'name' => 'nama',

            'email' => 'email',

            'role' => 'peran',

            'akses_semua_wilayah' => 'akses semua wilayah',

            'tim_pelaksana_ids' => 'tim pelaksana',

            'password' => 'password',

        ]);



        $validated['akses_semua_wilayah'] = $request->boolean('akses_semua_wilayah');



        if ($validated['role'] === User::ROLE_OPERATOR

            && ! $validated['akses_semua_wilayah']

            && blank($validated['tim_pelaksana_ids'] ?? null)) {

            throw \Illuminate\Validation\ValidationException::withMessages([

                'tim_pelaksana_ids' => 'Pilih minimal satu tim pelaksana atau aktifkan akses semua wilayah.',

            ]);

        }



        return $validated;

    }

}

