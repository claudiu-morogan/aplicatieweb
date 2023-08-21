create user 'extern'@'%' identified by 'extern';

grant all privileges on aplicatieweb.* to 'extern'@'%';

use aplicatieweb;

create table users (
    id integer primary key auto_increment,
    username text,
    password text,
    email text
);

insert into users(username, password, email) values (
    'claudiu',
    'claudiu',
    'claudiumorogan@gmail.com'
);
