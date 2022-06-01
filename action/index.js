const { spawn } = require('child_process')
const { Transform } = require('stream')
const { Buffer } = require('buffer')

const core = require('@actions/core')

// Default shell invocation used by GitHub Action 'run:'
const shellArgs = ['--noprofile', '--norc', '-eo', 'pipefail', '-c']

class RecordStream extends Transform {
	constructor () {
		super()
		this._data = Buffer.from([])
	}

	get output () {
		return this._data
	}

	_transform (chunk, encoding, callback) {
		this._data = Buffer.concat([this._data, chunk])
		callback(null, chunk)
	}
}

function cmd(command) {
	return new Promise((resolve, reject) => {
		const outRec = new RecordStream()
		const errRec = new RecordStream()

		const cmd = spawn('bash', [...shellArgs, command])

		cmd.stdout.pipe(outRec)
		cmd.stderr.pipe(errRec)

		cmd.on('error', error => {
			reject(error)
		})

		cmd.on('close', code => {
			resolve({
				code: code,
				stdout: outRec.output.toString(),
				stderr: errRec.output.toString()
			})
		})
	})
}

async function occ(command) {
	return await cmd(`./occ ${command}`)
}

async function ensureApp() {
	let {stdout} = await occ('app:list');
	if (!stdout.includes('blueprint')) {
		console.log('installing blueprint')
		await cmd(`curl -L https://github.com/icewind1991/blueprint/releases/download/v0.1.0/blueprint.tar.gz | tar -xz -C apps`)
		await occ(`app:enable --force blueprint`)
		await occ(`blueprint:enable`)
	}
}

async function run (blueprint) {
	await ensureApp()

	let {code, stdout} = await occ(`blueprint:apply ${blueprint}`)
	console.log(stdout)
	if (code !== 0) {
		core.setFailed('failed to apply blueprint')
	} else {
		console.log('blueprint applied')
	}
}

run(core.getInput('blueprint'))
	.catch(error => core.setFailed(error.message))
