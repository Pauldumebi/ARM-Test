import classes from './Navbar.module.css';
import Logo from '../../../images/the-call-center.svg';
import NavItem from '../NavItem/NavItem';
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
				{!isAuth && <NavItem route="/login" label="Login" />}
				{isAuth && (
					<button
						onClick={() => {
							localStorage.removeItem('ccAuth');
							history.push('/');
						}}
					>
						Log Out
					</button>
				)}
			</ul>
		</nav>
	);
};

export default Navbar;
