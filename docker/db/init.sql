-- Allow the app user to create/use the test database (gym_test) as well.
GRANT ALL PRIVILEGES ON `gym%`.* TO 'gym'@'%';
FLUSH PRIVILEGES;
