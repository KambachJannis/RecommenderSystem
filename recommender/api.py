# Recommender Service API

# Import framework
from flask import Flask, request
from flask_restful import Resource, Api
import sys
import subprocess

# Instantiate the app
app = Flask(__name__)
api = Api(app)


class Content(Resource):
	def get(self):
		recipe = request.args.get('recipe')
		amount = request.args.get('amount')
		try:
			output = subprocess.check_output([sys.executable, "content.py", recipe, amount])
			output = output.decode("utf-8")[1:-2]
			output = output.split(', ')
		except subprocess.CalledProcessError as e:
			raise RuntimeError("command '{}' return with error (code {}): {}".format(e.cmd, e.returncode, e.output))
		return {'recipes': output}


class UserSave(Resource):
	def get(self):
		recipe = request.args.get('recipe')
		rating = request.args.get('rating')
		user = request.args.get('user')
		try:
			output = subprocess.check_output([sys.executable, "user.py", recipe, rating, user])
			output = output.decode("utf-8")[:-2]
		except subprocess.CalledProcessError as e:
			raise RuntimeError("command '{}' return with error (code {}): {}".format(e.cmd, e.returncode, e.output))
		return {'recipe': output}


class User(Resource):
	def get(self):
		user = request.args.get('user')
		try:
			output = subprocess.check_output([sys.executable, "user.py", "none", "none", user])
			output = output.decode("utf-8")[:-2]
		except subprocess.CalledProcessError as e:
			raise RuntimeError("command '{}' return with error (code {}): {}".format(e.cmd, e.returncode, e.output))
		return {'recipe': output}


# Create routes
api.add_resource(Content, '/content')
api.add_resource(UserSave, '/usersave')
api.add_resource(User, '/user')

# Run the application
if __name__ == '__main__':
	app.run(host='0.0.0.0', port=80, debug=True)
