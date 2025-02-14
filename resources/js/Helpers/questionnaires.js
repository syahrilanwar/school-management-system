export default [
    {
        sort_number: 1,
        type: 'text',
        question:
            'Jika siswa menderita/menjalani perawatan salah satu penyakit di atas, mohon cantumkan catatan khusus',
        answer: null,
    },
    {
        sort_number: 2,
        type: 'radio',
        question: 'Apakah ada anggota keluarga lain menderita penyakit yang sama?',
        answer: null,
        children: [
            {
                sort_number: 1,
                type: 'text',
                question: 'Keterangan',
                answer: null,
            },
        ],
    },
    {
        sort_number: 3,
        type: 'radio',
        question: 'Apakah anak pernah dioperasi?',
        answer: null,
        children: [
            {
                sort_number: 1,
                type: 'text',
                question: 'Mengapa dan kapan?',
                answer: null,
            },
        ],
    },
    {
        sort_number: 4,
        type: 'text',
        question: 'Rincian keterbatasan aktivitas fisik yang dimiliki?',
        answer: null,
    },
    {
        sort_number: 5,
        type: 'radio',
        question: 'Apakah siswa sedang dalam perawatan medis?',
        answer: null,
        children: [
            {
                sort_number: 1,
                type: 'text',
                question: 'Harap sebutkan nama pengobatan dan frekuensinya?',
                answer: null,
            },
            {
                sort_number: 2,
                type: 'text',
                question: 'Reaksi yang biasa muncul ketika pengobatan?',
                answer: null,
            },
        ],
    },
    {
        sort_number: 6,
        type: 'radio',
        question: 'Alergi?',
        answer: null,
        children: [
            {
                sort_number: 1,
                type: 'text',
                question: 'Harap sebutkan?',
                answer: null,
            },
        ],
    },
    {
        sort_number: 7,
        type: 'date',
        question: 'Tanggal vaksinasi tetanus terakhir',
        answer: null,
    },
    {
        sort_number: 8,
        type: 'radio',
        question: 'Memakai kacamata/lensa kontak?',
        answer: null,
    },
    {
        sort_number: 9,
        type: 'radio',
        question:
            'Apakah siswa saat ini (atau sebelumnya) pernah memiliki kebutuhan pendidikan khusus (misal: Bimbingan Sekolah / Dirujuk kepada Psikolog Pendidikan / Dokter / Spesialis Anak)?',
        answer: null,
    },
];
