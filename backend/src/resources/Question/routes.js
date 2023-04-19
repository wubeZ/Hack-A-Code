const router = require('express').Router()
const questionController = require('./controller')

router
.route('/')
.get(questionController.getAllQuestion)
.post(questionController.create)

router
.route('/:id')
.get(questionController.getQuestion)
.put(questionController.updateQuestion)
.delete(questionController.deleteQuestion)

module.exports = router;