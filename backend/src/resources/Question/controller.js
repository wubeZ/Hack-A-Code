const db = require('../../services/db')

const create = (req, res, next) => {
    const data = {
        title : req.body.title,
        description : req.body.description
    }
    const { title, description } = data;
    db.connect().then((db) => {
        db.run(
          'INSERT INTO question (title, description) VALUES (?, ?)',
          title,
          description,
         (err) => {
            if (err){
                console.error(err.message)
                return res.status(404).json({message: err.message});
            }    
        console.log("Succesfully Created Question");
        res.status(200).json({ message: 'Succesfully Created Question' });
        }
        );
      }).catch((error) => {
        console.error(error);
        res.status(404).json({message: err.message})
      });

}

const getQuestion = (req, res, next) => {
    const user_id = req.params.id;
    db.connect().then((db) =>{
        db.all(`SELECT * FROM question WHERE id=?`,[user_id],(err,data) => {
            if (err){
                console.error(err.message)
                return res.status(404).json({message: err.message});
            }
            console.log("Succesfully got Question")
            res.status(200).json(data);
        });
    })
    .catch((error) => {
        console.error(error);
        res.status(404).json({message: err.message})
      });

}



const getAllQuestion = (req, res, next) => {
    db.connect().then((db) =>{
        db.all(`SELECT * FROM question`,[],(err,data) => {
            if (err){
                console.error(err.message)
                return res.status(404).json({message: err.message});
            }
            console.log("Succesfully got All Question")
            res.status(200).json(data);
        });
    })
    .catch((error) => {
        console.error(error);
        res.status(404).json({message: err.message})
      });
}


const deleteQuestion = (req, res, next ) => {
    const user_id = req.params.id
    db.connect().then((db) =>{
        db.run(`DELETE FROM question WHERE id=?`, [user_id], (err) => {
            if (err){
                console.error(err.message)
                return res.status(404).json({message: err.message});
            } 
            console.log("Succesfully Deleted Question");
            res.status(200).json({message : 'Succesfully Deleted Question'})
        });
    })
    .catch((error) => {
        console.error(error);
        res.status(404).json({message: err.message})
      });

}

const updateQuestion = (req, res, next) => {
    const user_id = req.params.id
    const updateFields = req.body

    if (Object.keys(updateFields).length === 0) {
        return res.status(400).json({message: 'No fields to update'})
    }

    let setClause = ''
    const validparameters = []

    for (const [field, value] of Object.entries(updateFields)) {
        setClause += `${field} = ?, `
        validparameters.push(value)
    }

    setClause = setClause.slice(0, -2)

    db.connect().then((db) =>{
        db.run(`UPDATE question SET ${setClause} WHERE id = ?`, [...validparameters, user_id], (err) => {
                if (err){
                    console.error(err.message)
                    return res.status(404).json({message: err.message});
                } 
                console.log("Succesfully Updated Question");
                res.status(200).json({message : 'Succesfully Updated Question'})
            });
        })
        .catch((error) => {
            console.error(error);
            res.status(404).json({message: err.message})
        });
   

}


module.exports = {
    create, 
    getAllQuestion,
    deleteQuestion,
    getQuestion,
    updateQuestion
};