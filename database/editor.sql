create table editor (

    id_editor serial primary key,
    username varchar(30) not null unique,
    password varchar(255) not null,
    no_wa varchar(20) not null,
    username_ig varchar(30) not null
);