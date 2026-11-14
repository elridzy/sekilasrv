create table detail_jasa (
    
    id_detail_jasa serial primary key,
    id_kategori_jasa integer not null,
    id_editor integer not null,
    gambar_portofolio varchar(255) not null,
    nama_jasa varchar(100) not null,
    deskripsi_jasa text,
    harga_jasa integer not null,
    tanggal_upload date not null default current_date,

    foreign key (id_kategori_jasa)
        references kategori_jasa(id_kategori_jasa),

    foreign key (id_editor)
        references editor(id_editor)
);