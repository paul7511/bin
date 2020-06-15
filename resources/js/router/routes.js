import Home from '../views/Home';
import Login from '../views/Login';
import Register from '../views/Register';

const routes = [
{
    path: '/',
    name: 'Home',
    component: Home
},
{
    path: '/login',
    name: 'Login',
    component: Login
},
{
    path: '/register',
    name: 'Register',
    component: Register
},
];

export default routes;