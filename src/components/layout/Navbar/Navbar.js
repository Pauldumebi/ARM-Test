import classes from './Navbar.module.css';
import Logo from '../../../images/the-call-center.svg';
import NavItem from '../NavItem/NavItem';
import CartIcon from '../CartIcon/CartIcon';
import { useHistory } from 'react-router-dom';

const Navbar = () => {
	const isAuth = localStorage.getItem('ccAuth');
	const history = useHistory();
	return (
		<nav className={classes.Nav}>
			<img src={Logo} alt="The Call Center" className={classes.Logo} />
			<ul>
				<NavItem route="/courses" label="Courses" />
				<NavItem route="/customers" label="Our Customer" />
				<NavItem route="/blog" label="Blog" />
				<NavItem route="/contact" label="Contact" />
				<CartIcon />
				{!isAuth && <NavItem route="/login" label="Login" />}
				{isAuth && (
					<button
						onClick={() => {
							localStorage.removeItem('ccAuth');
							history.push('/');
						}}
						className={classes.LogOut}
					>
						Log Out
					</button>
				)}
			</ul>
		</nav>
	);
};

export default Navbar;
