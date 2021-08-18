export const findItem = (arrObject, id) => {
	const item = arrObject.filter((obj) => obj.courseID === id);

	return item;
};

export const totalCost = (arr) => {
	console.log(arr);
	const totalSum = arr.reduce((acc, item) => acc + parseInt(item.price), 0);

	return totalSum;
};
