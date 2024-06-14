const express = require('express');
const app = express();
const PORT = process.env.PORT || 3000;
const mysql = require('mysql');
const req = "Select * from distributeur;";

const connection = mysql.createConnection({
	host: '172.20.233.94',
	user: 'baguette',
	password: 'baguette',
	database: 'distribaguette'
});

connection.connect((err) => {
	if (err) throw err;
	console.log('Connected to the database');
});

// Définir une route pour la racine ("/")
app.get('/:distributeurId/:etat/:nb_baguette', (req, res) => {
	const distributeurId = req.params.distributeurId;
	const etat = req.params.etat;
	const stockId = distributeurId;
	const nb_baguette = req.params.nb_baguette;
	const query1 = `update distributeur set etat='${etat}' where id=${distributeurId}`;
	const query2 = `update stock set nb_baguette=${nb_baguette} where id=${stockId}`;
  
	connection.query(query1, (err, results1) => {
	  if (err) throw err;
  
	  connection.query(query2, (err, results2) => {
		if (err) throw err;
		res.json({ results1, results2 });
	  });
	});
  });
  
  


app.listen(PORT, () => {
  console.log(`Server is running on port ${PORT}`);
});