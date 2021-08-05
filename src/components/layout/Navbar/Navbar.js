import classes from './Navbar.module.css';
import Logo from '../../../images/the-call-center.svg';
import NavItem from '../NavItem/NavItem';

const Navbar = () => {
	return (
		<nav className={classes.Nav}>
			<img src={Logo} alt="The Call Center" className={classes.Logo} />
			<ul>
				<NavItem route="/courses" label="Courses" />
				<NavItem route="/customers" label="Our Customer" />
				<NavItem route="/blog" label="Blog" />
				<NavItem route="/contact" label="Contact" />
				<NavItem route="/login" label="Login" />
			</ul>
		</nav>
	);
};

export default Navbar;
