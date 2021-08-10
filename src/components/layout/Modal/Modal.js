import classes from './Modal.module.css';

const Modal = (props) => {
	return (
		<div className={classes.Modal} onClick={props.hideModal}>
			{props.children}
		</div>
	);
};

export default Modal;
