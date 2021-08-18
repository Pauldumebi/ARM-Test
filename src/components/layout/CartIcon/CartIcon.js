import classes from './CartIcon.module.css';
import { ReactComponent as Cart } from '../../../images/shopping-cart.svg';
import { useStateContext } from '../../../context/context';
import { Link } from 'react-router-dom';

const CartIcon = () => {
	const { cart } = useStateContext();
	return (
		<Link to="/cart" className={classes.CartIcon}>
			<Cart />
			<span>{cart.length}</span>
		</Link>
	);
};

export default CartIcon;
