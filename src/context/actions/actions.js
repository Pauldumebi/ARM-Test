import * as actions from './actionTypes';

export const addToCart = (id) => {
	return {
		type: actions.ADD_TO_CART,
		courseID: id,
	};
};

export const removeFromCart = (id) => {
	return {
		type: actions.REMOVE_FROM_CART,
		courseID: id,
	};
};

export const setAllCourses = (allCourses) => {
	return {
		type: actions.SET_ALL_COURSES,
		courses: allCourses,
	};
};

export const clearCart = () => {
	return {
		type: actions.CLEAR_CART,
	};
};

export const toCheckout = () => {
	return {
		type: actions.TO_CHECKOUT,
	};
};
