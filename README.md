# kumikatapp
PHP project with CSS and HTML for the front-end for a web app that enables martial arts academies to register classes, students, payments and plan simple workouts to save some time. This project uses an MVC pattern, XAMPP for the Apache and MySQL (PHPmyadmin) servers, OOP, sessions and cookies. Netbeans is the IDE that was used to generate the code.

# Deployment:
For deployment you'll need XAMPP in order to simulate the Apache and MySQL servers. As soon as you install the software, turn on both servers to access the website on localhost/kumikatapp (create a folder in xampp/htdocs for the app). Open bbdd.txt document in the root folder of the "kumikatapp", then enter the full code in phpMyAdmin's SQL section. Now you'll have some tables to try stuff.

# How to use/interpret the code: 

The website was deployed using the Apache and MySQL server from XAMPP, by uploading the project folder to XAMPP's htdocs directory. Immediately afterwards, the statements from the **bbdd.txt document** in the root folder of the "kumikatapp" project were applied in **phpMyAdmin**. This includes both globally used data, like techniques, and some data that's subject to change, like users. 

Some student INSERTs have already been used, allowing us to conduct some quick tests, such as planning a class with an odd number of students. 

Initially, both the teacher role and the School will be defined manually. This means that a future client will contact the developer to have their school and a teacher created, which will then allow them to add students and even other teachers. The reason for this approach is to prevent a perverse incentive for cybercriminals or trolls to create fake profiles if any user could simply create schools and teachers. In the future, this process will become more sophisticated, with a payment gateway automatically triggering the creation of schools and teachers.

# Understanding objects used
Every new register in the database starts with the use of a static method of the respective class that returns an object. The constructor is defined in order to test some conditions before creating a new register. A static method is also used to return an object that represents the desired register of the database. After the object is created, additional methods can be utilized with the intention of adding extra actions such as pairing the students together.

# Understanding sessions and cookies
Some session variables are used for the purpose of keeping some info between each page. An example of this is the Persona instance that represents the logged user. For the sake of convenience, a cookie is also used to store username. Therefore, there are methods that are almost equivalent, they just use cookies or sessions variables. To get logged user's username, cookie variable is strongly recommended as it's simpler and more reliable. 
