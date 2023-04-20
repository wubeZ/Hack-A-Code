const router = require('express').Router()
const questionController = require('./controller')
const auth = require('./../../middlewares/auth')

router
.route('/')
.get(questionController.getAllQuestion)
.post(questionController.create)

router
.route('/filter')
.get(questionController.filterQuestion)

router
.route('/:id')
.get(questionController.getQuestion)
.put(auth, questionController.updateQuestion)
.delete(auth, questionController.deleteQuestion)

module.exports = router;