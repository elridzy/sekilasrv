create table editor {

    id_editor serial primary key,
    username varchar(50) not null unique,
    pasword varchar(255) not null,
    no_wa varchar(20) not null
}