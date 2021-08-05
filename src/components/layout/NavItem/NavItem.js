import { NavLink } from 'react-router-dom';
import classes from './NavItem.module.css';

const NavItem = (props) => {
	return (
		<li className={classes.NavItem}>
			<NavLink exact to={props.route} activeClassName={classes.ActiveLink}>
				{props.label}
			</NavLink>
		</li>
	);
};

export default NavItem;
