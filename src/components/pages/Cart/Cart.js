import classes from './Cart.module.css';
import { useStateContext } from '../../../context/context';
import { Link } from 'react-router-dom';
import { usePaystackPayment } from 'react-paystack';
import { useState } from 'react';

const Cart = () => {
	const { cart, total } = useStateContext();
	const userInfo = JSON.parse(localStorage.getItem('ccAuth'));
	const [config, setConfig] = useState({
		name: userInfo.companyName,
		email: userInfo.email,
		amount: total,
		publicKey: 'pk_test_4e331046a3e4ffdddac742a4670ae0b1b98c0b3f',
	});
	const initializePayment = usePaystackPayment(config);

	const onSuccess = () => {
		alert('Payment Successful');
	};

	const onClose = () => [alert('Payment not complemeted')];
	return (
		<section className={classes.Cart}>
			<h4 className={classes.Heading}>My Cart</h4>
			{cart.length > 0 ? (
				cart.map((item) => (
					<div key={item.courseID} className={classes.CartItem}>
						<img
							src="../../../images/reactredux.jpeg"
							alt={item.courseName}
							className={classes.CartItemImage}
						/>
						<h6>{item.courseName}</h6>
						<button>remove from cart</button>
					</div>
				))
			) : (
				<div>
					<h6>Your cart is empty</h6>
					<Link to="/courses">Buy a course here</Link>
				</div>
			)}
			{cart.length > 0 && (
				<>
					<div className={classes.Total}>
						<h6>Total:</h6> <p>$: {total}.00</p>
					</div>
					<button
						className={classes.CheckoutBtn}
						onClick={() => initializePayment(onSuccess, onClose)}
					>
						Proceed to checkout
					</button>
				</>
			)}
		</section>
	);
};

export default Cart;
