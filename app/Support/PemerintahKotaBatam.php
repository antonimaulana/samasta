<?php



namespace App\Support;



class PemerintahKotaBatam

{

    /**

     * @return list<array{name: string, jabatan: string, image: string, accent_bg: string, accent_ring: string, photo_class?: string, photo_frame_class?: string}>

     */

    public static function pimpinan(): array
    {
        return self::pimpinanStatic();
    }



    /**

     * @return array{visi: string, misi: string}

     */

    public static function visiMisi(): array
    {
        return self::visiMisiStatic();
    }



    /**

     * @return list<array{name: string, jabatan: string, image: string, accent_bg: string, accent_ring: string, photo_class?: string, photo_frame_class?: string}>

     */

    public static function pimpinanStatic(): array

    {

        return [

            [

                'name' => 'H. Amsakar Ahmad',

                'jabatan' => 'Walikota Batam',

                'image' => 'images/pimpinan/walikota.png',

                'accent_bg' => 'bg-violet-100',

                'accent_ring' => 'ring-violet-200/60',

                'photo_frame_class' => 'items-end justify-center',

                'photo_class' => 'h-full w-auto max-w-[92%] object-contain object-bottom drop-shadow-sm',

            ],

            [

                'name' => 'Li Claudia Chandra',

                'jabatan' => 'Wakil Walikota Batam',

                'image' => 'images/pimpinan/wakil-walikota.png',

                'accent_bg' => 'bg-sky-100',

                'accent_ring' => 'ring-sky-200/60',

                'photo_frame_class' => 'items-end justify-center pt-1',

                'photo_class' => 'h-[96%] w-auto max-w-[88%] object-contain object-bottom drop-shadow-sm',

            ],

            [

                'name' => 'Drs. Eryudhi Apriadi',

                'jabatan' => 'Kepala Dinas Perumahan, Kawasan Permukiman dan Pertamanan',

                'image' => 'images/pimpinan/kadis-perkim.png',

                'accent_bg' => 'bg-emerald-100',

                'accent_ring' => 'ring-emerald-200/60',

                'photo_frame_class' => 'items-end justify-center',

                'photo_class' => 'h-full w-auto max-w-[92%] object-contain object-bottom drop-shadow-sm',

            ],

        ];

    }



    /**

     * @return array{visi: string, misi: string}

     */

    public static function visiMisiStatic(): array

    {

        return [

            'visi' => 'Terwujudnya Batam sebagai Bandar Dunia Madani yang Modern dan Sejahtera.',

            'misi' => 'Mendorong terciptanya tata kelola pemerintahan yang bersih, pelayanan publik yang prima, serta pembangunan berkelanjutan demi kesejahteraan masyarakat Kota Batam.',

        ];

    }

}


