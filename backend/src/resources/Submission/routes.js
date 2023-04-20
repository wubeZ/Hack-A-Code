const router = require('express').Router()
const submissionController = require('./controller')
const auth = require('./../../middlewares/auth')

router
.route('/')
.get(auth, submissionController.getAllSubmission)
.post(auth, submissionController.create)

router
.route('/filter')
.get(auth, submissionController.filterSubmission);

router
.route('/:id')
.get(auth, submissionController.getSubmission)
.put(auth, submissionController.updateSubmission)
.delete(auth, submissionController.deleteSubmission)

module.exports = router;