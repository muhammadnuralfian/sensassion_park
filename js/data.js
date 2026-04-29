/**
 * data.js — Shared Data Store
 * Sensasion
 */

const DB_KEYS = {
  wisata:'sns_wisata', ulasan:'sns_ulasan', event:'sns_event',
  galeri:'sns_galeri', harga:'sns_harga', settings:'sns_settings', fasilitas:'sns_fasilitas',
};

const DEFAULT_DATA = {
  wisata:[
    {id:1,nama:'Kolam Pemancingan',    icon:'bi-fish',        deskripsi:'Kolam ikan segar dengan berbagai jenis ikan pilihan. Cocok untuk semua usia, dari pemula hingga mahir.',aktif:true},
    {id:2,nama:'Kolam Renang Keluarga',icon:'bi-water',       deskripsi:'Kolam renang bersih dan terawat untuk dewasa & anak-anak. Pengawas selalu tersedia selama jam operasional.',aktif:true},
    {id:3,nama:'Gazebo',               icon:'bi-house-heart', deskripsi:'Area bersantai di tepi kolam dengan pemandangan indah. Bisa disewa untuk acara keluarga atau gathering.',aktif:true},
    {id:4,nama:'Kantin & Kuliner',     icon:'bi-cup-hot',     deskripsi:'Tersedia kantin dengan menu pilihan lezat dan harga terjangkau. Nikmati makan di tepi kolam yang sejuk.',aktif:true},
  ],
  ulasan:[
    {id:1,nama:'Budi Santoso', rating:5,komen:'Tempatnya asik banget! Ikannya gampang kena, gazebonya nyaman. Pasti balik lagi sama keluarga.',        tanggal:'2024-11-15',tampil:true},
    {id:2,nama:'Siti Rahayu',  rating:5,komen:'Kolam renangnya bersih dan aman buat anak-anak. Harganya juga sangat terjangkau. Recommended!',         tanggal:'2024-11-20',tampil:true},
    {id:3,nama:'Ahmad Fauzi',  rating:4,komen:'Suasananya enak, cocok buat santai bareng teman. Kantin makanannya enak juga. Mantap deh!',             tanggal:'2024-12-01',tampil:true},
    {id:4,nama:'Dewi Lestari', rating:5,komen:'Anak-anak suka banget! Renang pagi, siang mancing, sore makan di kantin. Paket komplit wisata keluarga.',tanggal:'2024-12-10',tampil:true},
    {id:5,nama:'Rizky Pratama',rating:4,komen:'Gazebonya nyaman, view ke kolam bagus. Cocok untuk keluarga. Harga sangat worth it!',                   tanggal:'2025-01-05',tampil:true},
  ],
  event:[
    {id:1,nama:'Lomba Mancing Bulanan',   icon:'bi-trophy',      deskripsi:'Event lomba mancing rutin 2x sebulan dengan berbagai hadiah menarik.',biaya:250000,jadwal:'2x per bulan (kondisional)',status:'aktif'},
    {id:2,nama:'Program Ekskul Renang',   icon:'bi-mortarboard', deskripsi:'Pagi hari ramai anak-anak ekskul sekolah. Suasana aman dan terawasi.',biaya:0,jadwal:'Setiap hari (09.00–12.00)',status:'aktif'},
    {id:3,nama:'Gathering & Acara Privat',icon:'bi-people-fill', deskripsi:'Gazebo bisa disewa untuk acara privat dengan reservasi lebih dulu.',biaya:0,jadwal:'On request',status:'aktif'},
  ],
  galeri:[
    {id:1,kategori:'pemancingan',judul:'Area Kolam Mancing',    icon:'bi-fish',           deskripsi:'Kolam mancing utama dengan berbagai jenis ikan',gambar:''},
    {id:2,kategori:'renang',     judul:'Kolam Renang Keluarga', icon:'bi-water',          deskripsi:'Kolam renang bersih untuk seluruh keluarga',gambar:''},
    {id:3,kategori:'pemancingan',judul:'Mancing Santai',        icon:'bi-sunset',         deskripsi:'Suasana mancing yang tenang dan asri',gambar:''},
    {id:4,kategori:'fasilitas',  judul:'Area Gazebo',           icon:'bi-house-heart',    deskripsi:'Gazebo nyaman di tepi kolam',gambar:''},
    {id:5,kategori:'aktivitas',  judul:'Keluarga Bahagia',      icon:'bi-people-fill',    deskripsi:'Momen berkualitas bersama keluarga tercinta',gambar:''},
    {id:6,kategori:'renang',     judul:'Ekskul Renang',         icon:'bi-person-arms-up', deskripsi:'Program ekskul renang sekolah pagi hari',gambar:''},
  ],
  harga:[
    {id:1,nama:'Kolam Renang', harga:25000, satuan:'per orang',  icon:'bi-water',       catatan:'Berlaku semua usia',         aktif:true, gambar:'https://images.unsplash.com/photo-1562774053-701939374585?w=600&q=80'},
    {id:2,nama:'Pemancingan',  harga:5000,  satuan:'per orang',  icon:'bi-fish',        catatan:'Harga ikan mengikuti pasar', aktif:true, gambar:'https://images.unsplash.com/photo-1500463959177-e0869687df26?w=600&q=80'},
    {id:3,nama:'Gazebo',       harga:50000, satuan:'per sesi',   icon:'bi-house-heart', catatan:'Hingga selesai digunakan',   aktif:true, gambar:'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=600&q=80'},
    {id:4,nama:'Umpan Mancing',harga:15000, satuan:'per paket',  icon:'bi-droplet',     catatan:'Tersedia langsung di lokasi',aktif:true, gambar:'https://images.unsplash.com/photo-1544551763-46a013bb70d5?w=600&q=80'},
    {id:5,nama:'Lomba Mancing',harga:250000,satuan:'per peserta',icon:'bi-trophy',      catatan:'2x per bulan, kondisional', aktif:true, gambar:'https://images.unsplash.com/photo-1469854523086-cc02fe5d8800?w=600&q=80'},
  ],
  settings:{
    nama_tempat:'Sensasion',
    tagline:'Tempat Terbaik untuk Waktu Berkualitas Bersama Keluarga',
    whatsapp:'6281234567890',
    instagram:'@sension_samarinda',
    lokasi:'Samarinda, Kalimantan Timur',
    jam_buka:'09.00 – 18.00 WITA',
    hari_libur:'Senin & Hari Besar Nasional',
    pengelola:'Pak Deni',
    tahun_berdiri:'2020',
  },
  fasilitas:[
    {id:1,nama_fasilitas:'Kolam Pemancingan',     deskripsi:'Kolam ikan segar dengan berbagai jenis ikan pilihan untuk semua usia.',gambar:''},
    {id:2,nama_fasilitas:'Kolam Renang Keluarga', deskripsi:'Kolam renang bersih dan terawat, aman untuk dewasa dan anak-anak.',gambar:''},
    {id:3,nama_fasilitas:'Gazebo Tepi Kolam',     deskripsi:'Area bersantai di tepi kolam dengan pemandangan indah dan sejuk.',gambar:''},
    {id:4,nama_fasilitas:'Kantin & Kuliner',      deskripsi:'Menu pilihan lezat dengan harga terjangkau di tepi kolam.',gambar:''},
  ],
};

const DB = {
  get(key){
    const raw=localStorage.getItem(DB_KEYS[key]);
    if(raw){try{return JSON.parse(raw);}catch(e){}}
    this.set(key,DEFAULT_DATA[key]);
    return JSON.parse(JSON.stringify(DEFAULT_DATA[key]));
  },
  set(key,data){
    localStorage.setItem(DB_KEYS[key],JSON.stringify(data));
    window.dispatchEvent(new CustomEvent('db:updated',{detail:{key}}));
  },
  newId(key){
    const d=this.get(key);
    if(!Array.isArray(d)||d.length===0)return 1;
    return Math.max(...d.map(x=>x.id))+1;
  },
  seedAll(){
    Object.keys(DEFAULT_DATA).forEach(key=>{
      if(!localStorage.getItem(DB_KEYS[key]))
        localStorage.setItem(DB_KEYS[key],JSON.stringify(DEFAULT_DATA[key]));
    });
  },
};

const DATA_VERSION='v3-sensasion-bs5';
if(localStorage.getItem('sns_data_version')!==DATA_VERSION){
  Object.values(DB_KEYS).forEach(k=>localStorage.removeItem(k));
  localStorage.setItem('sns_data_version',DATA_VERSION);
}
DB.seedAll();

function formatRupiah(n){
  return 'Rp '+Number(n).toLocaleString('id-ID');
}
function escHtml(s){
  if(!s)return'';
  return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function renderIcon(icon){
  return `<i class="bi ${escHtml(icon)}"></i>`;
}
function renderStars(r){
  let s='';
  for(let i=1;i<=5;i++) s+=`<i class="bi ${i<=r?'bi-star-fill':'bi-star'}" style="color:var(--gold);font-size:.9rem;"></i>`;
  return s;
}
function formatDate(d){
  return new Date(d).toLocaleDateString('id-ID',{day:'numeric',month:'long',year:'numeric'});
}
